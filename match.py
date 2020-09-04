import cv2
import numpy as np
import sys
import os
# from matplotlib import pyplot as plt

def detectNumber(img_gray, templateFile):
	# print(templateFile)
	template = cv2.imread(templateFile, 0)
	w, h = template.shape[::-1]

	res = cv2.matchTemplate(img_gray, template, cv2.TM_CCOEFF_NORMED)
#	print(np.shape(res), len(res))

	threshold = 0.80
	# print(res)
	loc = np.where(res >= threshold)

#	print(np.shape(loc), len(loc))
#	print(loc)
#	exit()
	return w, h, loc, res

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

numbers = [1, 2, 3, 4, 5, 6, 7, 8, 9, 0]
def mergeBoxes(img_rgb, img_gray):
	allBoxes = []
	for num in numbers:
		digits = os.path.dirname(os.path.abspath(__file__)) + '/digits/'
		digitFile = digits + str(num) + '.png'
		print(digitFile)
		oneW, oneH, oneBoxes, res = detectNumber(img_gray, digitFile)
		print(digitFile, len(oneBoxes))
		# print(oneW, oneH, len(oneBoxes))
		oneBoxes = dissociate(oneW, oneH, oneBoxes)
		# print(oneBoxes)
		drawBoxes(img_rgb, oneW, oneH, oneBoxes)
		for bx in oneBoxes:
			print("confidence", res[bx[1], bx[0]])
			allBoxes.append({
				"width": oneW,
				"height": oneH,
				"x": bx[0],
				"y": bx[1],
				"num": num,
				"confidence": res[bx[1], bx[0]]
			})
	return allBoxes

def drawBoxes(img_rgb, sevenW, sevenH, boxes):
	for pt in boxes:
		cv2.rectangle(img_rgb, pt, (pt[0] + sevenW, pt[1] + sevenH), (0,0,255), 1)

def main():
	if not sys.argv[1]:
		raise Error("first arg must be an image file path")

	print(sys.argv[1])
	img_rgb = cv2.imread(sys.argv[1])
	img_gray = cv2.cvtColor(img_rgb, cv2.COLOR_BGR2GRAY)

	allBoxes = mergeBoxes(img_rgb, img_gray)
	print(list(str(p["num"]) + ':' + str(p["x"]) for p in allBoxes))

	allBoxes.sort(key=lambda el: el["x"])	# from left to right
	print(list(str(p["num"]) + ':' + str(p["x"]) + '(' + str(p['confidence']) + ')' for p in allBoxes))

	bestConfidence = []
	prev = None
	for b in allBoxes:
		if prev is None:
			print(b['num'], 'prev is None')
			bestConfidence.append(b)
			prev = b
			continue
		x2 = prev['x'] + prev['width']
		if prev['x'] <= b['x'] <= x2:
			# conflict, check confidence
			print(b['num'], prev['x'], '<=', b['x'], '<=', x2)
			if b['confidence'] > prev['confidence']:
				bestConfidence.pop()
				bestConfidence.append(b)
			else:
				pass
				#bestConfidence.pop()
				#bestConfidence.append(prev)
		else:
			bestConfidence.append(b)
		prev = b

	print(list(str(p["num"]) + ':' + str(p["x"]) + '(' + str(p['confidence']) + ')' for p in bestConfidence))

	meter = list(str(t["num"]) for t in bestConfidence)
	meter = "".join(meter)
	try:
		iMeter = int(meter)
		if len(meter) > 5:
			iMeter /= 10
		print(meter, iMeter)

		cv2.imwrite('res2.png', img_rgb)
	except ValueError:
		print('0 0')
		exit(10)

if __name__ == "__main__":
	main()
