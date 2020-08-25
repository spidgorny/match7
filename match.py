import cv2
import numpy as np
# from matplotlib import pyplot as plt

img_rgb = cv2.imread('/var/motion/output.png')
img_gray = cv2.cvtColor(img_rgb, cv2.COLOR_BGR2GRAY)

def detectNumber(templateFile):
	# print(templateFile)
	template = cv2.imread(templateFile, 0)
	w, h = template.shape[::-1]

	res = cv2.matchTemplate(img_gray,template,cv2.TM_CCOEFF_NORMED)
	threshold = 0.8
	# print(res)
	loc = np.where( res >= threshold)
	return w, h, loc

def dissociate(oneW, oneH, oneBoxes):
	new = []
	for box in zip(*oneBoxes[::-1]):
		# print('x=', box[0], ' y=', box[1])
		if not test_overlap(new, box, oneW, oneH):
			new.append(box)
	return new

def test_overlap(arr, box, width, height):
	"""
	Check if box[0,1]=x,y is touching any of the other
	boxes inside arr: box[]
	"""
	box = {
		"left": box[0],
		"right": box[0] + width,
		"top": box[1],
		"bottom": box[1] + height,
	}
	for el in arr:
		el = {
			"left": el[0],
			"right": el[0] + width,
			"top": el[1],
			"bottom": el[1] + height
		}
		if boxOverlap(box, el) or boxOverlap(el, box):
			return True
	return False

def boxOverlap(box, el):
	xOK = box["left"] > el["right"]
	yOK = box["right"] < el["left"]
	x2K = box["top"] > el["bottom"]
	y2K = box["bottom"] < el["top"]
	# print(xOK, box["left"], '>', el["right"])
	# print(yOK, box["right"], '<', el["left"])
	# print(x2K, box["top"], '>', el["bottom"])
	# print(y2K, box["bottom"], '<', el["top"])
	# top-left in the box
	# or bottom-right corner in the box
	return not ((xOK or yOK) or (x2K or y2K))

def drawBoxes(sevenW, sevenH, boxes):
	for pt in boxes:
	    cv2.rectangle(img_rgb, pt, (pt[0] + sevenW, pt[1] + sevenH), (0,0,255), 1)


def test_test_overlap():
	new = [[40, 192]]
	width = 46
	height = 74
	check = [39, 193]
	overlap = test_overlap(new, check, width, height)
	print(new[0], new[0][0] + width, new[0][1] + height)
	print(check, check[0] + width, check[1] + height)
	print(overlap)

# test_test_overlap()
# exit()

allBoxes = []
numbers = [1, 2, 4, 5, 6, 7]
for num in numbers:
	oneW, oneH, oneBoxes = detectNumber(str(num) + '.png')
	# print(oneW, oneH, len(oneBoxes))
	oneBoxes = dissociate(oneW, oneH, oneBoxes)
	# print(oneBoxes)
	drawBoxes(oneW, oneH, oneBoxes)
	for bx in oneBoxes:
		allBoxes.append({
			"width": oneW,
			"height": oneH,
			"x": bx[0],
			"y": bx[1],
			"num": num,
		})

allBoxes.sort(key=lambda el: el["x"])
meter = list(str(t["num"]) for t in allBoxes)
meter = "".join(meter)
meter = int(meter)/10
print(meter)


cv2.imwrite('res.png',img_rgb)

