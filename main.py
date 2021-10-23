from arrayToJson import convert_json
from wall_scheme import get_walls
from arrayToJson import convert_json
import argparse

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
parser = argparse.ArgumentParser(description='Process shp files.')
parser.add_argument('--config', metavar='C', type=str,
                    help='an config gile to use', default='press2/config.json')

args = parser.parse_args()
grid = get_walls(args.config)
convert_json(grid)
