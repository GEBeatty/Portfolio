"""
    ChatRM

    A minimal chat platform where users can create rooms and talk with other users
"""

import time
import os
import json
from datetime import datetime, timedelta
from flask import Flask, request, session, url_for, redirect, render_template, abort, g, flash, _app_ctx_stack
from werkzeug import check_password_hash, generate_password_hash

from models import db, User, Chatroom, Message

app = Flask(__name__)

DEBUG = True
SECRET_KEY = 'secret key bois'
# Create database location
SQLALCHEMY_DATABASE_URI = 'sqlite:///' + os.path.join(app.root_path, 'chat.db')
SQLALCHEMY_TRACK_MODIFICATIONS = False #here to silence deprecation warning

app.config.from_object(__name__)

db.init_app(app)

# To create database from cmd
@app.cli.command('initdb')
def initdb_command():
    db.create_all()
    print('Initialized chat database.')
    # Create test user
    test_pass = generate_password_hash('test')
    test = User(user_name='test', user_phash=test_pass)
    print("test is {}".format(test))
    db.session.add(test)
    db.session.commit()

@app.before_request
def before_request():
    g.user = None
    if 'user_id' in session:
        g.user = User.query.filter_by(user_id=session['user_id']).first()

@app.route('/', methods=['GET', 'POST'])
def login():
    # If logged in, go to chat main
    if g.user:
        return redirect(url_for('main_chat'))
    error = None
    if request.method == 'POST':
        user = User.query.filter_by(user_name=request.form['log_username']).first()
        # Does user exist in database?
        if user is None:
            error = 'Invalid username or password'
        elif not check_password_hash(user.user_phash, request.form['log_password']):
            error = 'Invalid username or password'
        else:
            # User logged in properly
            session['user_id'] = user.user_id
            return redirect(url_for('main_chat'))
    # If not logged in, there is login and register
    return render_template('login.html', error=error)

@app.route('/register', methods=['GET', 'POST'])
def register():
    if g.user:
        return redirect(url_for('main_chat'))
    error = None
    if request.method == 'POST':
        user = User.query.filter_by(user_name=request.form['reg_username']).first()
        # Does user exist in database?
        if user:
            error = 'That username is already taken'
        else:
            # Create a new user
            new_user = User(user_name=request.form['reg_username'], user_phash=generate_password_hash(request.form['reg_password']))
            db.session.add(new_user)
            db.session.commit()
            session['user_id'] = new_user.user_id
            return redirect(url_for('main_chat'))
    # Render register template
    return render_template('register.html', error=error)

@app.route('/chatrooms', methods=['GET', 'POST'])
def main_chat():
    if g.user is None:
        return redirect(url_for('login'))
    # Get all chatrooms and display
    chat_arr = None
    chat_arr = db.session.query(User).join(Chatroom.creator).with_entities(User.user_name, Chatroom.chat_id, Chatroom.chat_name).first()
    chats_available = None
    if chat_arr is None:
        chats_available = 1
    else:
        chat_arr = db.session.query(User).join(Chatroom.creator).with_entities(User.user_name, Chatroom.chat_id, Chatroom.chat_name).all()
    return render_template('chatrooms.html', username=g.user.user_name, chat_arr=chat_arr, chats_available=chats_available)

@app.route('/messages/<chat_id>')
def chat_room(chat_id):
    if g.user is None:
        return redirect(url_for('login'))
    # Does chatroom exist?
    current_chat = Chatroom.query.filter_by(chat_id=chat_id).first()
    if current_chat is None:
        flash("Sorry, that chatroom doesn't exist")
        return redirect(url_for('main_chat'))
    session['chat_id'] = chat_id
    mess_arr = None
    mess_check = None
    # Get messages for current chat in date descending
    mess_arr = Message.query.filter_by(fk_chat_id=chat_id).order_by(Message.mess_time.desc()).first()
    if mess_arr is None:
        mess_check = 1
    else:
        session['curr_time'] = str(mess_arr.mess_time)
        mess_arr = Message.query.filter_by(fk_chat_id=chat_id).order_by(Message.mess_time.desc()).all()
    return render_template('messages.html', username=g.user.user_name, mess_arr=mess_arr, mess_check=mess_check, chat_title=current_chat.chat_name, chat_id=current_chat.chat_id, user_id=g.user.user_id, creator_id=current_chat.fk_user_id)

@app.route('/newpost', methods=['POST'])
def new_post():
    # Get attributes for message
    iso_time = datetime.strptime(request.form['message_time'], '%Y-%m-%d %H:%M:%S.%f')
    new_message = Message(mess_text=request.form['message_text'], mess_time=iso_time)
    new_message.fk_user_id = g.user.user_id
    g.user.messages.append(new_message)
    new_message.fk_chat_id = int(session['chat_id'])
    # Add to database
    db.session.add(new_message)
    db.session.commit()
    return "Ok!"

@app.route('/allposts')
def get_posts():
    # Find chat id
    chat_id = int(session['chat_id'])
    mess_chat = Chatroom.query.filter_by(chat_id=chat_id).first()
    if mess_chat is None:
        return "We're sorry, the user has deleted this chatroom."
    # Find any messages
    mess_arr = Message.query.filter_by(fk_chat_id=chat_id).order_by(Message.mess_time.desc()).first()
    if mess_arr is None:
        session['curr_time'] = str(datetime.now())
        return "There are no messages in this chat yet..."
    # First, get new messages
    most_rec = datetime.strptime(session['curr_time'], '%Y-%m-%d %H:%M:%S.%f')
    all_arr = Message.query.filter_by(fk_chat_id=chat_id).filter(Message.mess_time > most_rec).order_by(Message.mess_time.desc()).all()
    new_time = Message.query.filter_by(fk_chat_id=chat_id).filter(Message.mess_time > most_rec).order_by(Message.mess_time.desc()).first()
    # If new_time is empty, there are no new messages
    if new_time is None:
        return "no_new"
    # Then, set new message time
    session['curr_time'] = str(new_time.mess_time)
    # And return the JSON string
    str1 = '[ '
    for row in all_arr:
        str1 = str1 + '{ '
        str1 = str1 + '"name":"' + row.author.user_name + '", '
        str1 = str1 + '"text":"' + row.mess_text + '", '
        str1 = str1 + '"time":"' + row.mess_time.strftime("%Y-%m-%d %H:%M:%S.%f")[:-3]
        str1 = str1 + '" }, '
    str1 = str1[:-2]
    str1 = str1 + ' ]'
    return str1

@app.route('/deleteroom')
def delete():
    if g.user is None:
        return redirect(url_for('login'))
    # check to make sure it's the right user
    curr_chat = Chatroom.query.filter_by(chat_id=session['chat_id']).first()
    # print("chat: "+str(curr_chat.fk_user_id)+"  user: " + str(g.user.user_id))
    if g.user.user_id != curr_chat.fk_user_id:
        return redirect(url_for('login'))
    # Delete the chat and its messages
    Message.query.filter_by(fk_chat_id=curr_chat.chat_id).delete()
    Chatroom.query.filter_by(chat_id=curr_chat.chat_id).delete()
    db.session.commit()
    # Redirect back to chatroom page
    return redirect(url_for('main_chat'))

@app.route('/new_chat', methods=['GET', 'POST'])
def new_chat():
    # Is user logged in?
    if g.user is None:
        return redirect(url_for('login'))
    if request.method == 'POST': # Creating a new chatroom
        new_chatroom = Chatroom(chat_name=request.form['new_chat_name'], fk_user_id=g.user.user_id)
        db.session.add(new_chatroom)
        g.user.chatrooms.append(new_chatroom)
        db.session.commit()
        flash("Chatroom Created!")
        return redirect(url_for('main_chat'))
    return render_template('new_chat.html', username=g.user.user_name)

@app.route('/logout')
def logout():
    # Remove data from session
    session.clear()
    flash('You were logged out')
    return redirect(url_for('login'))