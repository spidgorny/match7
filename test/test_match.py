import inspect
import os
import sys

import match

sys.path.insert(0, '..')
sys.path.append(os.path.dirname(os.getcwd()))
print(sys.path)

currentdir = os.path.dirname(os.path.abspath(inspect.getfile(inspect.currentframe())))
parentdir = os.path.dirname(currentdir)
sys.path.insert(0, parentdir)


def test_test_overlap():
    new = [[40, 192]]
    width = 46
    height = 74
    check = [39, 193]
    overlap = match.test_overlap(new, check, width, height)
    print(new[0], new[0][0] + width, new[0][1] + height)
    print(check, check[0] + width, check[1] + height)
    print(overlap)
    assert overlap


def test_match_1_file():
    match.match('../motion/01-20200904061640-snapshot.png')
