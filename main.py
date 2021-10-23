from arrayToJson import convert_json
from wall_scheme import get_walls
from arrayToJson import convert_json

grid = get_walls(0.6, 0.3, 90, 80, 180, 150, False, 2, "pres2")
convert_json(grid)