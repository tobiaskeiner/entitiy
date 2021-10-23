def convert_json(tileSet):
    import json
    from random import choice

    tileSet.reverse()
    flatTileSet = [item for sublist in tileSet for item in sublist]

    print(flatTileSet)
    tileId = {
        0: [0],#0 = nichts
        1: [65, 66, 67, 68, 69, 70, 71, 72, 73], #1 wird mit verschiedenen wall texturen überschrieben
        2:[206,207,208], #2 wall top
        3:[135,136,137]# parket boden
    }
    #with open('ConvertedArray.txt', 'w') as f:
    #   f.write(f'{my_array}')
    #a_file.close()
    editedFlatTielSet = [choice(tileId[Item]) for Item in flatTileSet]
    
    layers = {
        "data": editedFlatTielSet,
        "height":len(tileSet),
        "id":19,
        "name":"Wall",
        "opacity":1,
        "type":"tilelayer",
        "visible":True,
        "width":len(tileSet[0]),
        "x":0,
        "y":0
    }
    with open("Map/testMap.json") as f:
        existing = json.load(f)
        
    existing["layers"].append(layers)
    with open('Map/testMap.json', 'w') as json_file:
        json.dump(existing, json_file, indent=4)