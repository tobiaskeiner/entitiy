use std::io::{Error as IoError, ErrorKind};

use actix_multipart::MultipartError;
use actix_web::Error as ActixError;

#[derive(Debug)]
pub enum Error {
    NoSpaceOnDevice,
    AddressInUse,
    NoSuchFileOrDirectory,
    NoContentDisposition,
    NoContentType,
    NoBoundary,
    NestedMultipart,
    MultipartError,
    NoDirectorySet,
    FilePathNoValidExtension,
    FileTooLarge,
    ActixError(ActixError),
    FilenameError,
    /// has stderr
    ExecutionError(String),
    SomethingElse(Box<dyn std::error::Error>)
}

impl std::fmt::Display for Error {
    fn fmt(&self, f: &mut std::fmt::Formatter<'_>) -> std::fmt::Result {
        match self {
            Self::NoSpaceOnDevice => write!(f, "no space on devide"),
            Self::AddressInUse => write!(f, "address in use"),
            Self::NoSuchFileOrDirectory => write!(f, "no such file or direcory"),
            Self::NoContentDisposition => write!(f, "no content disposition"),
            Self::NoContentType => write!(f, "no content type in multipart"),
            Self::NoBoundary => write!(f, "no multipart boundary"),
            Self::NestedMultipart => write!(f, "nested multipart is not supported"),
            Self::MultipartError => write!(f, "error while parsing multipart content"),
            Self::NoDirectorySet => write!(f, "no directory set"),
            Self::FilePathNoValidExtension => write!(f, "file path has no valid extension"),
            Self::FileTooLarge => write!(f, "at least one file is too large"),
            Self::ActixError(error) => write!(f, "{}", error),
            Self::FilenameError => write!(f, "filename error"),
            Self::ExecutionError(error) => write!(f, "execution error: {}", error),
            Self::SomethingElse(error) => write!(f, "something else {}", error),
        }
    }
}

impl Error {
    pub fn safe_for_client(&self) -> bool {
        match self {
            Error::NoSpaceOnDevice => false,
            Error::AddressInUse => false,
            Error::NoSuchFileOrDirectory => false,
            Error::NoContentDisposition => true,
            Error::NoContentType => true,
            Error::NoBoundary => true,
            Error::NestedMultipart => true,
            Error::MultipartError => true,
            Error::NoDirectorySet => false,
            Error::FilePathNoValidExtension => true,
            Error::FileTooLarge => true,
            Error::ActixError(_) => false,
            Error::FilenameError => false,
            Error::ExecutionError(_) => false,
            Error::SomethingElse(_) => false,
        }
    }

    pub fn safe_display(&self) -> String {
        if self.safe_for_client() {
            format!("{}", self)
        } else {
            eprintln!("internal server error: {}", self);
            "internal server error".to_string()
        }
    }
}

impl std::error::Error for Error {}

impl From<IoError> for Error {
    fn from(error: IoError) -> Self {
        match error.kind() {
            ErrorKind::OutOfMemory => Error::NoSpaceOnDevice,
            ErrorKind::AddrInUse => Error::AddressInUse,
            ErrorKind::AddrNotAvailable => Error::AddressInUse,
            ErrorKind::NotFound => Error::NoSuchFileOrDirectory,
            _ => Error::SomethingElse(Box::new(error))
        }
    }
}

impl From<MultipartError> for Error {
    fn from(error: MultipartError) -> Self {
        match error {
            MultipartError::NoContentType => Error::NoContentType,
            MultipartError::ParseContentType => Error::NoContentType,
            MultipartError::Boundary => Error::NoBoundary,
            MultipartError::Nested => Error::NestedMultipart,
            MultipartError::Incomplete => Error::MultipartError,
            MultipartError::Parse(_) =>  Error::MultipartError,
            MultipartError::Payload(_) =>  Error::MultipartError,
            MultipartError::NotConsumed =>  Error::MultipartError,
        }
    }
}

impl From<ActixError> for Error {
    fn from(error: ActixError) -> Self {
        Error::ActixError(error)
    }
}