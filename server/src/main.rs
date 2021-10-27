use std::{fs::{File, create_dir, remove_dir_all}, io::{self, Read, Write}, path::Path, process::Command};

use actix_multipart::Multipart;
use actix_session::{CookieSession, Session};
use actix_web::{App, HttpServer, get, http::header::{DispositionParam, DispositionType}, post, web::{self, Data, HttpResponse}};
use handlebars::Handlebars;
use rand::{Rng, distributions::Alphanumeric, thread_rng};
use serde_derive::{Serialize, Deserialize};
use futures::{StreamExt, TryStreamExt};
use serde_json::json;

const BASE_DIRECTORY: &'static str = "./tmp";
const EXEC: &'static str = "../main.py";

const X_MULT: &'static str = "xMult";
const Y_MULT: &'static str = "yMult";
const X_OFFSET: &'static str = "xOffset";
const Y_OFFSET: &'static str = "yOffset";
const X_LIMIT: &'static str = "xLimit";
const Y_LIMIT: &'static str = "yLimit";
const WALL_HEIGHT: &'static str = "wallHeight";
const IS_WALL: &'static str = "isWall";
const DIRECTORY: &'static str = "directory";
const NAME: &'static str = "name";
const CONFIG_FILE: &'static str = "conf.json";
const MAP_OUTPUT: &'static str = "confMap.json";

#[derive(Debug, Serialize, Deserialize)]
#[serde(rename_all = "camelCase")]
struct Parameters {
    #[serde(default = "default_mult")]
    pub x_mult: f32,
    #[serde(default = "default_mult")]
    pub y_mult: f32,
    #[serde(default = "default_offset")]
    pub x_offset: f32,
    #[serde(default = "default_offset")]
    pub y_offset: f32,
    #[serde(default = "default_limit")]
    pub x_limit: f32,
    #[serde(default = "default_limit")]
    pub y_limit: f32,
    #[serde(default = "default_wall_height")]
    pub wall_height: i32,
    #[serde(default = "default_is_wall")]
    pub is_wall: bool,
    #[serde(skip)]
    pub directory: Option<String>,
    pub name: Option<String>,
    pub message: Option<String>,
    pub success: bool,
}

fn default_mult() -> f32 {1.0}
fn default_offset() -> f32 {0.0}
fn default_limit() -> f32 {100.0}
fn default_wall_height() -> i32 {2}
fn default_is_wall() -> bool {false}

impl Parameters {
    fn from_session(session: &Session) -> Parameters {
        Parameters {
            x_mult: session.get(X_MULT).ok().flatten().unwrap_or_else(default_mult),
            y_mult: session.get(Y_MULT).ok().flatten().unwrap_or_else(default_mult),
            x_offset: session.get(X_OFFSET).ok().flatten().unwrap_or_else(default_offset),
            y_offset: session.get(Y_OFFSET).ok().flatten().unwrap_or_else(default_offset),
            x_limit: session.get(X_LIMIT).ok().flatten().unwrap_or_else(default_limit),
            y_limit: session.get(Y_LIMIT).ok().flatten().unwrap_or_else(default_limit),
            wall_height: session.get(WALL_HEIGHT).ok().flatten().unwrap_or_else(default_wall_height),
            is_wall: session.get(IS_WALL).ok().flatten().unwrap_or_else(default_is_wall),
            directory: session.get(DIRECTORY).ok().flatten(),
            name: session.get(NAME).ok().flatten(),
            message: None,
            success: false,
        }
    }

    fn enrich_path(&mut self, session: &Session) {
        self.directory = session.get(DIRECTORY).ok().flatten();
        if let None = self.directory {
            let rng = thread_rng();
            let chars: String = rng.sample_iter(Alphanumeric)
                .take(32)
                .map(char::from)
                .collect();
            let path = Path::new(BASE_DIRECTORY).join(chars);
            create_dir(&path).unwrap();
            let name = path.to_string_lossy().to_string();
            session.set(DIRECTORY, &name).unwrap();
            self.directory = Some(name);
        }
    }
}

#[get("/")]
async fn index(hb: Data<Handlebars<'_>>, session: Session) -> HttpResponse {
    let parameters = Parameters::from_session(&session);
    let body = hb.render("index", &parameters).unwrap();

    HttpResponse::Ok().body(body)
}

async fn interpret_multipart(parameters: &mut Parameters, mut data: Multipart) -> Option<()> {
    let directory = parameters.directory.as_ref()?;
    while let Ok(Some(mut field)) = data.try_next().await {
        let content_type = field.content_disposition()?;
        let filename = content_type
            .get_filename()
            .map(|name| Path::new(name));
        if let Some(path) = filename {
            if let None = parameters.name {
                parameters.name = path
                    .file_stem()
                    .and_then(|os_str| os_str.to_str())
                    .map(|str| str.to_string());
            }

            let extension = path.extension()?.to_str()?;
            if ["shp", "shx", "dbf"].contains(&extension) {
                let mut size = 0;

                let filename = format!("input.{}", extension);
                let mut file = File::create(Path::new(directory).join(filename)).unwrap();

                while let Some(chunk) = field.next().await {
                    let bytes = chunk.ok()?;
                    size += bytes.len();
                    if size > 10_000_000 {
                        return None;
                    }
                    file.write_all(&bytes).unwrap();
                }
            }
        } else if let DispositionType::FormData = content_type.disposition {
            let mut name = None;
            for parameter in content_type.parameters {
                match parameter {
                    DispositionParam::Name(n) => name = Some(n),
                    _ => {}
                }
            }
            if let Some(name) = name {
                let mut value = Vec::new();

                if ![X_MULT, Y_MULT, X_OFFSET, Y_OFFSET, X_LIMIT, Y_LIMIT, WALL_HEIGHT, IS_WALL].contains(&name.as_str()) {
                    continue;
                }

                while let Some(chunk) = field.next().await {
                    let bytes = chunk.ok()?;
                    value.extend_from_slice(&bytes);
                    if value.len() > 10 {
                        break;
                    }
                }

                let value = std::str::from_utf8(&value);
                
                if let Ok(value) = value {
                    match name.as_str() {
                        X_MULT => if let Some(mult) = value.parse().ok() { parameters.x_mult = mult}
                        Y_MULT => if let Some(mult) = value.parse().ok() { parameters.y_mult = mult}
                        X_OFFSET => if let Some(offset) = value.parse().ok() { parameters.x_offset = offset}
                        Y_OFFSET => if let Some(offset) = value.parse().ok() { parameters.y_offset = offset}
                        X_LIMIT => if let Some(limit) = value.parse().ok() { parameters.x_limit = limit}
                        Y_LIMIT => if let Some(limit) = value.parse().ok() { parameters.y_limit = limit}
                        WALL_HEIGHT => if let Some(height) = value.parse().ok() { parameters.wall_height = height}
                        IS_WALL => if let Some(is_wall) = value.parse().ok() { parameters.is_wall = is_wall}
                        _ => {}
                    }
                }
            }
        }
    }
    Some(())
}

#[post("/")]
async fn index_post(hb: Data<Handlebars<'_>>, session: Session, data: Multipart) -> HttpResponse {
    let mut parameters = Parameters::from_session(&session);
    parameters.enrich_path(&session);
    if let (Some(()), Some(directory)) = (interpret_multipart(&mut parameters, data).await, parameters.directory.as_ref()) {
        if let Some(name) = parameters.name.as_ref() {
            session.set(NAME, name).unwrap()
        }
        let config = json!({
            "xMult":       parameters.x_mult,
            "yMult":       parameters.y_mult,
            "xOffset":     parameters.x_offset,
            "yOffset":     parameters.y_offset,
            "xLimit":      parameters.x_limit,
            "yLimit":      parameters.y_limit,
            "wallHeight":  parameters.wall_height,
            "isWall":      parameters.is_wall,
            "printMatrix": false,
            "file":        Path::new(&directory).join("input").to_string_lossy(),
        });
        let conf_file = Path::new(&directory).join(CONFIG_FILE);
        {
            let mut file = File::create(&conf_file).unwrap();
            file.write_all(config.to_string()[..].as_bytes()).unwrap();
        } // using block so the file is being closed here
        let output = Command::new("python")
            .args([EXEC, "--config", conf_file.to_str().unwrap()])
            .output()
            .unwrap();
        if output.stderr.len() > 0 {
            println!("{}", std::str::from_utf8(&output.stderr).unwrap());
        }
        let result = Path::new(directory).join(MAP_OUTPUT);
        if File::open(result).is_ok() {
            parameters.message = Some("your file is ready to be downloaded".to_string());
            parameters.success = true;
        } else {
            parameters.message = Some("the conversion failed, either you forgot a file or you contact the administrator".to_string());
        }
        let body = hb.render("index", &parameters).unwrap();
        HttpResponse::Ok().body(body)
    } else {
        parameters.message = Some("could not execute query".to_string());
        let body = hb.render("index", &parameters).unwrap();
        HttpResponse::Ok().body(body)
    }
}

#[get("/{name}.json")]
async fn get_json(session: Session, web::Path(name): web::Path<String>) -> HttpResponse {
    let stored_name = session.get::<String>(NAME).ok().flatten();
    let directory = session.get::<String>(DIRECTORY).ok().flatten();
    println!("{:?}, {:?}", stored_name, directory);
    if let (Some(stored_name), Some(directory)) = (stored_name, directory) {
        if stored_name == name {
            let filename = Path::new(&directory).join(MAP_OUTPUT);
            let mut file = File::open(filename).unwrap();
            let mut content = String::new();
            file.read_to_string(&mut content).unwrap();
            HttpResponse::Ok().body(content)
        } else {
            HttpResponse::NotFound().body("The server could not find the file, if you used any special characters, try to upload the file again without them. Otherwise, contact the administrator")
        }
    } else {
        HttpResponse::InternalServerError().body("Idk what the hell is going on. Maybe your request timed out")
    }
}

#[actix_web::main]
async fn main() -> io::Result<()> {
    let mut handlebars = Handlebars::new();
    handlebars.set_strict_mode(true);
    handlebars.set_dev_mode(cfg!(debug_assertions));
    handlebars
        .register_templates_directory(".hbs", "static/templates")
        .unwrap();
    let handlebars_ref = Data::new(handlebars);

    // ignore if directory does not exist
    let _ = remove_dir_all(BASE_DIRECTORY);
    create_dir(BASE_DIRECTORY).unwrap();

    HttpServer::new(move || {
        App::new()
            .wrap(CookieSession::signed(&[0;32]).secure(false))
            .app_data(handlebars_ref.clone())
            .service(index)
            .service(index_post)
            .service(get_json)
    })
        .bind("127.0.0.1:8081")?
        .run()
        .await
}
