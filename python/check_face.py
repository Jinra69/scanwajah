import sys
import json
import os
import cv2

def main():
    if len(sys.argv) < 2:
        print(json.dumps({"has_face": False, "error": "Path foto tidak diberikan"}))
        return

    image_path = sys.argv[1]

    if not os.path.isfile(image_path):
        print(json.dumps({"has_face": False, "error": "File tidak ditemukan"}))
        return

    img = cv2.imread(image_path)
    if img is None:
        print(json.dumps({"has_face": False, "error": "Gagal membaca file gambar"}))
        return

    gray = cv2.cvtColor(img, cv2.COLOR_BGR2GRAY)
    gray = cv2.equalizeHist(gray)

    cascade_path = cv2.data.haarcascades + "haarcascade_frontalface_default.xml"
    face_cascade = cv2.CascadeClassifier(cascade_path)

    faces = face_cascade.detectMultiScale(
        gray,
        scaleFactor=1.1,
        minNeighbors=5,
        minSize=(60, 60)
    )

    if len(faces) == 0:
        print(json.dumps({"has_face": False}))
        return

    # Ambil wajah terbesar saja (paling dekat ke kamera)
    faces = sorted(faces, key=lambda f: f[2] * f[3], reverse=True)
    x, y, w, h = faces[0]

    print(json.dumps({
        "has_face": True,
        "face_count": int(len(faces)),
        "box": {"x": int(x), "y": int(y), "w": int(w), "h": int(h)}
    }))

if __name__ == "__main__":
    main()