#coding:utf-8
# http://qiita.com/lrf141/items/ff1462c5c6b7b3207775

import numpy as np
import cv2

def pixelate():
    #顔探索用のカスケード型分類器を取得
    #haarcascade_frontalface_default.xmlのパスを渡す 
    face_cascade = cv2.CascadeClassifier("haarcascade_frontalface_default.xml")

    img = cv2.imread("2013-10-27 13.36.31.jpg")
    result = cv2.imread("2013-10-27 13.36.31.jpg")

    #読み込んだ画像をグレースケールに変換
    gray = cv2.cvtColor(img,cv2.COLOR_RGB2GRAY)

    #分類器で顔を認識する
    face = face_cascade.detectMultiScale(gray,1.3,5)

    if 0 < len(face):

        print "get face"

        for (x,y,w,h) in face:

            #顔の部分だけ切り抜いてモザイク処理をする
            cut_img = img[y:y+h,x:x+w]
            cut_face = cut_img.shape[:2][::-1]
            #10分の1にする
            cut_img = cv2.resize(cut_img,(cut_face[0]/10, cut_face[0]/10))
            #画像を元のサイズに拡大
#            cut_img = cv2.resize(cut_img,cut_face,interpolation = cv2.cv.CV_INTER_NN)
            cut_img = cv2.resize(cut_img,cut_face,interpolation = 0)

            #モザイク処理した部分を重ねる
            result[y:y+h,x:x+w] = cut_img
            cv2.imwrite("mosaic.jpg",result)
    else:

        print "no face"

if __name__ == '__main__':
    pixelate()