#!/usr/bin/env python3

import hashlib
import binascii
import urllib.parse
import sys
import json

AMAZING_DIFFICULTY = 0
MEDIUM_DIFFICULTY = 1
EASY_DIFFICULTY = 2

class Difficulty:
    def __init__(self, id, text):
        self.id = id
        self.text = text

    def __str__(self):
        return self.text

difficulties = {
    EASY_DIFFICULTY: Difficulty(EASY_DIFFICULTY, "Easy"),
    MEDIUM_DIFFICULTY: Difficulty(MEDIUM_DIFFICULTY, "Medium"),
    AMAZING_DIFFICULTY: Difficulty(AMAZING_DIFFICULTY, "Amazing"),
}

scores = sys.argv[1]
#scores = "63657265616c310a370a646963740a6c6973740a7475706c650a340a6934393730300a69340a73360a6b65657033727334300a653863646533396538663765613338633033383762346134636266623930646666613763653036657475706c650a340a6934353833360a69340a73360a6b65657033727334300a326130383431356637306263383935333333363539303234643436633361623766376635656530357475706c650a340a6934343735330a69340a73360a6b65657033727334300a616161643338366338636237656330313664316334383133656531643530333434323861343633317475706c650a340a6933353539340a69330a73360a6b65657033727334300a613533343038373636313563393332376531396635613938616534306535303938336563363463637475706c650a340a6933353533360a69330a73360a6b65657033727334300a30626663373635393237343130386236316235323139356464616430666638613664316536323138310a72310a69320a350a72320a72330a72340a72350a72360a72300a"

scores = binascii.unhexlify(scores)
scores = scores.decode('utf-8')
scores = json.loads(scores)

#print scores

for difficulty in scores.keys():
    try:
        difficulty = difficulties[int(difficulty)]
    except KeyError:
        continue
    for score, stars, name, _hash in scores[str(difficulty.id)]:
        print(score, stars, name, _hash, difficulty.id, "eof")