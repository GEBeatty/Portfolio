import datetime
from flask_sqlalchemy import SQLAlchemy

db = SQLAlchemy()

class User(db.Model):
    user_id = db.Column(db.Integer, primary_key=True)
    user_name = db.Column(db.String(50), nullable=False)
    user_phash = db.Column(db.String(128), nullable=False)

    messages = db.relationship('Message', backref='author')
    chatrooms = db.relationship('Chatroom',backref='creator')

    def __init__(self, user_name, user_phash):
      self.user_name = user_name
      self.user_phash = user_phash

    def __repr__(self):
      return '<User {}>'.format(self.user_name)


class Chatroom(db.Model):
    chat_id = db.Column(db.Integer, primary_key=True)
    chat_name = db.Column(db.String(50), nullable=False)

    fk_user_id = db.Column(db.Integer, db.ForeignKey('user.user_id'))
    chat_messages = db.relationship('Message', backref='chat_room')

    def __init__(self, chat_name, fk_user_id):
      self.chat_name = chat_name
      self.fk_user_id = fk_user_id

    def __repr__(self):
      return '<Chatroom {}, ID {}'.format(self.chat_name, self.chat_id)


class Message(db.Model):
    mess_id = db.Column(db.Integer, primary_key=True)
    mess_text = db.Column(db.String(500), nullable=False)
    mess_time = db.Column(db.DateTime, default=datetime.datetime.now, nullable=False)

    fk_user_id = db.Column(db.Integer, db.ForeignKey('user.user_id'), nullable=False)
    fk_chat_id = db.Column(db.Integer, db.ForeignKey('chatroom.chat_id'), nullable=False)

    def __init__(self, mess_text, mess_time):
      self.mess_text = mess_text
      self.mess_time = mess_time

    def __repr__(self):
      return '( "user":{}, "text":{}, "time":{} )'.format(self.author.user_name, self.mess_text, self.mess_time)