import sys
import json
import os
import cv2
import numpy as np

FACE_SIZE = (200, 200)
# Skor LBPH = jarak (semakin kecil semakin mirip). Di atas ini dianggap "tidak dikenal".
DISTANCE_THRESHOLD = 70

face_cascade = cv2.CascadeClassifier(
    cv2.data.haarcascades + "haarcascade_frontalface_default.xml"
)


def extract_face(image_path):
    img = cv2.imread(image_path)
    if img is None:
        return None

    gray = cv2.cvtColor(img, cv2.COLOR_BGR2GRAY)
    gray = cv2.equalizeHist(gray)

    faces = face_cascade.detectMultiScale(
        gray, scaleFactor=1.1, minNeighbors=5, minSize=(60, 60)
    )
    if len(faces) == 0:
        return None

    faces = sorted(faces, key=lambda f: f[2] * f[3], reverse=True)
    x, y, w, h = faces[0]
    face = gray[y:y + h, x:x + w]
    face = cv2.resize(face, FACE_SIZE)
    return face


def main():
    if len(sys.argv) < 3:
        print(json.dumps({"status": "error", "message": "Argumen tidak lengkap"}))
        return

    scan_path = sys.argv[1]
    uploads_dir = sys.argv[2]

    if not os.path.isfile(scan_path):
        print(json.dumps({"status": "error", "message": "File scan tidak ditemukan"}))
        return

    if not os.path.isdir(uploads_dir):
        print(json.dumps({"status": "error", "message": "Folder uploads tidak ditemukan"}))
        return

    scan_face = extract_face(scan_path)
    if scan_face is None:
        print(json.dumps({"status": "error", "message": "Wajah tidak terdeteksi pada hasil scan"}))
        return

    valid_ext = (".jpg", ".jpeg", ".png")
    filenames = [f for f in os.listdir(uploads_dir) if f.lower().endswith(valid_ext)]

    if not filenames:
        print(json.dumps({"status": "error", "message": "Belum ada data wajah terdaftar"}))
        return

    train_faces = []
    labels = []
    label_to_filename = {}

    for idx, fname in enumerate(filenames):
        fpath = os.path.join(uploads_dir, fname)
        face = extract_face(fpath)
        if face is None:
            continue
        train_faces.append(face)
        labels.append(idx)
        label_to_filename[idx] = fname

    if not train_faces:
        print(json.dumps({"status": "error", "message": "Tidak ada wajah valid di data terdaftar"}))
        return

    recognizer = cv2.face.LBPHFaceRecognizer_create()
    recognizer.train(train_faces, np.array(labels))

    predicted_label, distance = recognizer.predict(scan_face)

    if distance > DISTANCE_THRESHOLD:
        print(json.dumps({
            "status": "not_found",
            "message": "Wajah tidak dikenali",
            "distance": float(distance)
        }))
        return

    # Konversi distance (semakin kecil semakin bagus) jadi confidence 0..1 (semakin besar semakin bagus)
    confidence = max(0.0, 1.0 - (distance / DISTANCE_THRESHOLD))

    print(json.dumps({
        "status": "success",
        "filename": label_to_filename[predicted_label],
        "confidence": round(confidence, 4),
        "distance": float(distance)
    }))


if __name__ == "__main__":
    main()