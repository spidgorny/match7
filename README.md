# Rotating numbers recognition using OpenCV matchTemplate

Goal: capture the image from the webcam and recognize the digits on the analog energy meter.

Source:

![](source.jpg)

## Step 1

Denoise and rotate the source image from the webcam.

	convert /var/motion/lastsnap.jpg -rotate 2 -auto-level -auto-gamma -noise 5 -median 5 -unsharp 5 -normalize /var/motion/output.png

Result:

![](denoise.jpg)

## Step 2

Prepare cut-out images for each digit.

![](0.png)
![](digits/1a.png)
![](digits/2.png)
![](3.png)
![](digits/4.png)
![](digits/5.png)
![](digits/6a.png)
![](digits/7.png)
![](digits/8.png)
![](digits/9.png)

## Step 3

Run python script to find the rectangles for each digit.

![](res.png)

The rectangles needs to be sorted from left to right.

The rectangles in order give away the numbers detected.

