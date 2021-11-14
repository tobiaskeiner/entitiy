import json
from pathlib import Path
from os import rename
import argparse

# Analyse Tiled JSON maps and optionally optimize (if not dry-run)
# 
# a) Find unused tilesets and remove them. Re-indexing not yet implemented.
# b) Find bounding box per layer and reduce (crop) data field with respective 
#    x and y offsets. This is actually not supported for finite maps, although
#    Tiled does accept such maps, WorkAdventure does not.

def cleanup(tileMap, dryRun):
    print('\n------------\nTilesets\n------------')
    unused = []
    for tileset in tileMap['tilesets']:
        i1 = tileset['firstgid']
        no = tileset['tilecount']
        used = any(any(ti in range(i1,i1+no) for ti in layer['data']) for layer in dataLayers(tileMap['layers']))
        if not used:
            unused.append(tileset)
        print('[{4}] {0}: {1}..{2} ({3} tiles)'.format(tileset['name'], i1, i1+no-1, no, '+' if used else '-'))
    if not dryRun:
        for ts in unused:
            tileMap['tilesets'].remove(ts)
    print('-------------\nUnused Tilesets: ' + ', '.join(ts['name'] for ts in unused))
    print('\n------------\nLayers\n------------')
    for layer in dataLayers(tileMap['layers']):
      if not any(layer['data']):
        print(layer['name'] + ': only empty tiles in data')
        continue
      (w,h,d) = (layer[attr] for attr in ('width', 'height', 'data'))
      assert w*h == len(d)
      y1 = next(((int) (rowStart/w) for rowStart in range(0,len(d),w) if any(d[rowStart+x] for x in range(0,w)) ), None)
      y2 = next(((int) (rowStart/w) for rowStart in reversed(range(0,len(d),w)) if any(d[rowStart+x] for x in range(0,w)) ), None)
      x1 = next((x for x in range(0,w) if any(d[i] for i in range(x,len(d),h)) ), None)
      x2 = next((x for x in reversed(range(0,w)) if any(d[i] for i in range(x,len(d),h)) ), None)
      print('{0}: x {1}-{2}, y {3}-{4}, {5}x{6} / {7}x{8}'.format(layer['name'], x1, x2, y1, y2, x2-x1+1, y2-y1+1, w, h))
      if not dryRun:
          data = [d[y+x] for y in range(w*y1, w*y2+w, w) for x in range(x1,x2+1) ]
          layer['data'] = data
          layer['x'] = x1
          layer['y'] = y1
          layer['width'] = x2-x1+1
          layer['height'] = y2-y1+1
    noData = [layer['name']  for layer in nonDataLayers(tileMap['layers'])]
    print('-----------------\nLayers without data: ' + ', '.join(noData)) # type group or objectgroup
      

def allLayers(layers, condition):
    for layer in layers: 
        if condition(layer):
            yield layer
        if 'layers' in layer:
            yield from allLayers(layer['layers'], condition)

def dataLayers(layers):
    return allLayers(layers, lambda l: 'data' in l) # l['type']=='tileLayer'

def nonDataLayers(layers):
    return allLayers(layers, lambda l: not 'data' in l)
    
if __name__ == '__main__':
    parser = argparse.ArgumentParser(description='Analyse and cleanup tile map Json')
    parser.add_argument('-d','--dry-run', dest='dryRun', action='store_true', help='report cleanup potential, do not change file')
    parser.add_argument('tilemap', nargs='?', default='press2/configMap.json', help='tile map JSON file')
    parser.set_defaults(dryRun=False)
    args = parser.parse_args()
    fileName  = args.tilemap
    p = Path(fileName)
    newFileName = ''.join([str(Path.joinpath(p.parent, p.stem)), '_clean',  p.suffix])
    with open(fileName) as f:
        tileMap = json.load(f)
    cleanup( tileMap, args.dryRun )
    if not args.dryRun:
        with open(newFileName, 'w') as f:
            json.dump( tileMap , f)  

