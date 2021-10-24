from json import load
from typing import List, Tuple

def get_walls(config_file):
    with open(config_file) as f:
        config = load(f)

    # multiplier for coordinates
    X_MULT: float = config['xMult']
    Y_MULT: float = config['yMult']

    # every point will get shifted by these values
    X_OFFSET: int = config['xOffset']
    Y_OFFSET: int = config['yOffset']

    # positive limit for coordinates
    # negative values will be discarded
    X_LIMIT: int = config['xLimit']
    Y_LIMIT: int = config['yLimit']

    # parameters for walls
    IS_WALL: bool = config['isWall']
    WALL_HEIGHT: int =config['wallHeight']

    # input file
    FILE: str = config['file']

    print_matrix: bool = config['printMatrix']

    import shapefile
    from grid import get_grid

    sf: shapefile.Reader = shapefile.Reader(FILE)

    X_MAX: int = round(X_LIMIT * X_MULT)
    Y_MAX: int = round(Y_LIMIT * Y_MULT)

    grid: List[List[bool]] = [[0 for _ in range(X_MAX)] for _ in range(Y_MAX + WALL_HEIGHT if IS_WALL else Y_MAX)]

    for shape in sf.iterShapes():
        last_point = None
        for point in shape.points:
            x: int = round((point[0] + X_OFFSET) * X_MULT)
            y: int = round((point[1] + Y_OFFSET) * Y_MULT)
            if 0 <= x <= X_MAX and 0 <= y <= Y_MAX:
                grid[y][x] = 1
                if last_point:
                    points: List[Tuple[int, int]] = get_grid((x, y), (last_point[0], last_point[1]))
                    for x, y in points:
                        grid[y][x] = 1
                last_point = (x, y)


    if IS_WALL:
        basis = [line.copy() for line in grid]
        for i in range(1, WALL_HEIGHT + 1):
            for y, line in enumerate(basis):
                for x, tile in enumerate(line):
                    if tile != 0:
                        grid[y + i][x] = 2 if WALL_HEIGHT == i else tile

    grid.reverse()

    if print_matrix:
        for array in grid:
            for element in array:
                if element == 1:
                    print("█", end='')
                elif element == 2:
                    print('▄', end='')
                else:
                    print(" ",end='')
            print()
    return grid
