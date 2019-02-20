# Chat Application

## Overview

1. **Users :**
	- First the user will be asked to enter a unique username, while the user is online this username alongside other details will be stored in the database so that the server will know all the users that are online, the database will be constantly updated with each request the user makes to the server, so that if there is no longer any requests the user is no longer online. 
	 
2. **Chat rooms :**
	- User can create a chat room that users can join either by searching for it of having the unique id (this depends on room type), the chat rooms will be deleted and all the messages in a certain amount of time after all users have left the room.
	- There is two types of chat room public and private, public rooms can be searched for by tags that define the type of topic that people discuss and can also be featured in the home page if the room has a lot of active users, private rooms can only be accessed with a unique id witch only the creator of the room hase and can share with other users.
		- **Rooms options :**
			- `allow mods` option for adding moderators to chat room.
			- `allow link` option to block users from sending links.
			- other options ... 
		- **Moderators :**
			- Mods can ban or mute users in the chat, the admin is the only one that can add mods. 

## Technicalities

1. **Users :**
	- The client will send a request to the server with the defined username, the client will alse save the username in the local machine, in the server this data will be stored in the 
database.
	- When ever the client sends a request to the server the 
users table will be updated to keep the user online, the server will check all the uservers every period of time to delet all the users that are no longer active.
2. **Rooms :**
	- The user will chose a name for the room and a type then the client will send a request to the server with the room data and the user data the server will responde with room information after it creats it, in the database the server will store the room name, a unique id, admin, options and creation time, now that the user is connected to the room there is two request that will be used, the first one is to get users and the seconde one is for messages, to get the online users the client will send a lon pull request to the server and whenever users table is updated for that room the server will respond with the new data that contains all the users online, for the messages request it will also be a long pull request that retrieves the messages and a normal post request to send messages.

## API

1. **Users :**
	- `[POST]` /api/users
		- Used to join a room
		- Request body  <br/>
			``` json
			{ 
			    "username": username,
			    "room_ID": room_id,
			}
			```
	- `[PUT]` /api/users/{user_id} **(Not Availble)**
		- Used to update user options

1. **Rooms :**
	- `[POST]` /api/rooms
		- Used to create a new room
		- Request body  <br/>
			``` json
			{ 
			    "name"     : room_name,
			    "username" : username,
			    "options"  : {
			       "accsess" : "private",
			       "tags"    : "tag1  tag2" || null
			    }
			}
			```
	- `[DELETE]` /api/rooms/{room_id}
		- Used to delete a room
		
	- `[PUT]` /api/rooms/{room_id} **(Not Availble)**
		- Used to update the rooms options

	- `[GET]` /api/rooms/{room_id}/users?number={number of current users}
		- Used to get online users
		
	- `[GET]` /api/rooms/featured **(Not Availble)**
		- Used to get featured rooms (its based on the number of online users)

	- `[GET]` /api/rooms/search **(Not Availble)**
		- Used to search rooms by tags
		
1. **Messages :**
	- `[GET]` /api/messages/{room_id}
		- Used to get all messages in the chat room
		- You can use the filter parameter to get just the new messages 
		`?time={last messege time ex. 2019-02-15 15:21:44}`
		
	- `[POST]` /api/messages/{room_id}
		- Used to send messages
		- Request body  <br/>
			``` json
			{
				"message": "Hello World!"
			}
			```
## Database Structure

<img src="https://i.imgur.com/O3VSpVI.png" />

## Files Structure
> This is the files and folders structure for the API

    .
    ├── api/                         
    │   ├── config/
    |   |      ├── database.php     # Database connection and config
    │   │      └── core.php         # Core api config
    │   ├── modules/  
    │   │      ├── users.php        # Users properties and methods 
    │   │      ├── messages.php     # Messages properties and methods 
    │   │      └── rooms.php        # Rooms properties and methods
    │   └── index.php               # All requests will be redirected to this file
    └── ...
 
