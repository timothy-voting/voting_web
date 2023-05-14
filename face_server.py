import socket
import face_recognition


def img_compare_faces(known_face_location, unknown_face_location):
    known_face = face_recognition.load_image_file(known_face_location)
    unknown_face = face_recognition.load_image_file(unknown_face_location)
    known_face_encoding = face_recognition.face_encodings(known_face)[0]
    unknown_encoding = face_recognition.face_encodings(unknown_face)[0]
    return face_recognition.compare_faces([known_face_encoding], unknown_encoding)


s = socket.socket()
print("Socket successfully created")

port = 8003

s.bind(('127.0.0.1', port))
print("socket binded to %s" % port)

# put the socket into listening mode
s.listen(5)
print("socket is listening")

while True:
    c, addr = s.accept()
    msg = c.recv(1020).decode()
    faces = msg.split(',')
    result = img_compare_faces(faces[0], faces[1])[0]
    if result:
        resp = 'true'
    else:
        resp = 'false'
    c.send(resp.encode())
    c.close()
