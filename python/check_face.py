import sys
import json
import face_recognition


def main():
    image_path = sys.argv[1]
    try:
        image = face_recognition.load_image_file(image_path)
        encodings = face_recognition.face_encodings(image)
        print(json.dumps({"has_face": len(encodings) > 0}))
    except Exception as e:
        print(json.dumps({"has_face": False, "error": str(e)}))


if __name__ == "__main__":
    main()
