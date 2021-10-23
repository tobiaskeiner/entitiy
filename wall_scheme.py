#! /usr/bin/env python3
def get_walls(X_MULT, Y_MULT, X_OFFSET, Y_OFFSET, X_LIMIT, Y_LIMIT, IS_WALL, WALL_HEIGHT, File_Name, print_matrix = False):
    from typing import List, Tuple
    """
    # multiplier for coordinates
    X_MULT: float = 0.6
    Y_MULT: float = 0.3

    # every point will get shifted by these values
    X_OFFSET: int = 90
    Y_OFFSET: int = 80

    # positive limit for coordinates
    # negative values will be discarded
    X_LIMIT: int = 180
    Y_LIMIT: int = 150

    # parameters for walls
    IS_WALL = False
    WALL_HEIGHT = 2
    """
    # input file
    FILE: str = File_Name

    import shapefile
    from grid import get_grid

    sf: shapefile.Reader = shapefile.Reader(FILE)

    X_MAX: int = round(X_LIMIT * X_MULT)
    Y_MAX: int = round(Y_LIMIT * Y_MULT)

    grid: List[List[bool]] = [[0 for _ in range(X_MAX)] for _ in range(Y_MAX + WALL_HEIGHT if IS_WALL else Y_MAX)]

    for shape in sf.iterShapes():
        if hasattr(shape, 'bbox'):
            coords: List[float] = shape.bbox
            assert len(coords) == 4, 'more or less than 4 ccordinate parts'
            start_x: int = round((coords[0] + X_OFFSET) * X_MULT)
            start_y: int = round((coords[1] + Y_OFFSET) * Y_MULT)
            end_x: int = round((coords[2] + X_OFFSET) * X_MULT)
            end_y: int = round((coords[3] + Y_OFFSET) * Y_MULT)
            if (
                    0 <= start_x < X_MAX
                and 0 <= start_y < Y_MAX
                and 0 <= end_x < X_MAX
                and 0 <= end_y < Y_MAX
            ):
                points: List[Tuple[int, int]] = get_grid((start_x, start_y), (end_x, end_y))
                for x, y in points:
                    grid[y][x] = 1
        elif hasattr(shape, 'points'):
            for point in shape.points:
                x: int = round((point[0] + X_OFFSET) * X_MULT)
                y: int = round((point[1] + Y_OFFSET) * Y_MULT)
                if 0 <= x <= X_MAX and 0 <= y <= Y_MAX:
                    grid[y][x] = 1
        else:
            print(dir(shape))
            raise NotImplementedError


    if IS_WALL:
        basis = [line.copy() for line in grid]
        for i in range(1, WALL_HEIGHT + 1):
            for y, line in enumerate(basis):
                for x, tile in enumerate(line):
                    if tile != 0 and grid[y + i][x] == 0:
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
