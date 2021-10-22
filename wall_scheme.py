#! /usr/bin/env python3

# multiplier for coordinates
X_MULT: float = 1
Y_MULT: float = 1

# every point will get shifted by these values
X_OFFSET: int = 45
Y_OFFSET: int = 73

# positive limit for coordinates
# negative values will be discarded
X_LIMIT: int = 90
Y_LIMIT: int = 25

# input file
FILE: str = 'merged'

import shapefile
from grid import get_grid

sf: shapefile.Reader = shapefile.Reader(FILE)

X_MAX: int = round(X_LIMIT * X_MULT)
Y_MAX: int = round(Y_LIMIT * Y_MULT)

grid: list[list[bool]] = [[False for _ in range(Y_MAX)] for _ in range(X_MAX)]

for shape in sf.iterShapes():
    coords: list[float] = shape.bbox
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
        points: list[tuple[int, int]] = get_grid((start_x, start_y), (end_x, end_y))
        for x, y in points:
            grid[x][y] = True

print(grid)
