from arrayToJson import convert_json
from wall_scheme import get_walls
from arrayToJson import convert_json

#params:
    #multiplier for coordinates X
    #multiplier for coordinates Y
    # every point will get shifted by these values X
    # every point will get shifted by these values Y
    # positive limit for coordinates X
    # positive limit for coordinates Y
    # is a wall || 2.5d
    # wall height
    # filename
grid = get_walls('press2/config.json')
convert_json(grid)
