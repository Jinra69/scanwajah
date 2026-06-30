import sys
import json
import os
import face_recognition


def encode_known_faces(uploads_dir):
    """Encode semua foto yang sudah terdaftar di folder uploads"""
    known_encodings = []
    known_data = []

    for filename in os.listdir(uploads_dir):
        if filename.lower().endswith(('.jpg', '.jpeg', '.png')):
            filepath = os.path.join(uploads_dir, filename)
            try:
                image = face_recognition.load_image_file(filepath)
                encodings = face_recognition.face_encodings(image)
                if len(encodings) > 0:
                    known_encodings.append(encodings[0])
                    known_data.append(filename)
            except Exception:
                continue

    return known_encodings, known_data


def recognize_face(scan_image_path, uploads_dir):
    unknown_image = face_recognition.load_image_file(scan_image_path)
    unknown_encodings = face_recognition.face_encodings(unknown_image)

    if len(unknown_encodings) == 0:
        return {"status": "error", "message": "Wajah tidak terdeteksi pada gambar"}

    unknown_encoding = unknown_encodings[0]

    known_encodings, known_data = encode_known_faces(uploads_dir)

    if len(known_encodings) == 0:
        return {"status": "error", "message": "Belum ada data wajah terdaftar"}

    results = face_recognition.compare_faces(known_encodings, unknown_encoding, tolerance=0.5)
    distances = face_recognition.face_distance(known_encodings, unknown_encoding)

    if True in results:
        best_match_index = distances.argmin()
        if results[best_match_index]:
            matched_file = known_data[best_match_index]
            return {
                "status": "success",
                "filename": matched_file,
                "confidence": float(1 - distances[best_match_index])
            }

    return {"status": "not_found", "message": "Wajah tidak dikenali / tidak cocok dengan data manapun"}


if __name__ == "__main__":
    scan_image_path = sys.argv[1]
    uploads_dir = sys.argv[2]

    result = recognize_face(scan_image_path, uploads_dir)
    print(json.dumps(result))
