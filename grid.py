#Method to get all tiles crossed by a line
def get_grid(start, end):
    x1, y1 = start
    x2, y2 = end

    #get rise of slope
    dx = x2 - x1
    dy = y2 - y1

    #check for angle above 45°
    is_steep = abs(dy) > abs(dx)

    #swap coordinates
    if is_steep:
        x1, y1 = y1, x1
        x2, y2 = y2, x2

    swapped = False

    if x1 > x2:
        x1, x2 = x2, x1
        y1, y2 = y2, y1
        swapped = True

    #get new rise of slope 
    dx = x2 - x1
    dy = y2 - y1

    #calculate error
    error = int(dx / 2.0)
    ystep = 1 if y1 < y2 else -1

    y = y1
    points = []
    for x in range(x1, x2 + 1):
        coord = (y, x) if is_steep else (x, y)
        points.append(coord)
        error -= abs(dy)
        if error < 0:
            y += ystep
            error += dx

    if swapped:
        points.reverse()
    return points

result = get_grid((1,1),(22,9))
print(result)