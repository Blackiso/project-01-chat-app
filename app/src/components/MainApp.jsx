import React from 'react';
import { observer } from 'mobx-react';
import RightSideBar from './RightSideBar';
import LeftSideBar from './LeftSideBar';
import MessagesContainer from './MessagesContainer';
import MessageInput from './MessageInput';
import RoomTitle from './RoomTitle';
import store from '../store/store';
import { toJS } from 'mobx';
import history from '../functions/history';
import Variables from '../functions/variablesCall';
import WS from '../functions/webSocket';

class MainApp extends React.Component{
    constructor(){
        super();
        this.returnData = this.returnData.bind( this );
    }
    componentDidMount(){
        let { room_ID } = this.props['match']['params'],
            { roomInfo } = store,
            joined = Boolean( Object.values(roomInfo).length );

        if( !room_ID ) history.push('/');
        else if( room_ID.length !== 15 ) history.push('/');
        else if( room_ID && !joined ) history.push(`/join_room/${ room_ID }`);
        else if(!joined ) history.push('/join_room/');
        else this.getData();
        
    }
    render(){
        return(
            <div className="main">
                <LeftSideBar />
                <div className="main-content">
                    <div className="chat-container">
                        <RoomTitle />
                        <MessagesContainer />
                        <MessageInput />
                    </div>
                </div>
                <RightSideBar />
            </div>
        )
    }
    getData(){
        let { ws } = Variables,
            { roomInfo } = store,
            { user_ID, room_ID } = roomInfo,
            { app_name } = Variables['layout'];
            this.socket = new WS(ws, [ user_ID, room_ID ]);
            
            store['socket'] = this.socket;
            this.socket.startListening( this.returnData );
            
        document.title = `${ app_name } | ${ roomInfo['room_name'] }`;
    }
    returnData(e){
        let { type, body } = JSON.parse( e['data'] );
        
        switch( type ){
            case 'user_joined': this.joinedUsers( body ); break;
            case 'user_left': this.userLeft( body ); break;
            case 'new_msg': this.addMessage( body ); break;
        }
    }
    addMessage( e ){
        let { messages } = store;
        messages = [ ...messages, ...e ];
        store['messages'] = messages;
    }
    userLeft( e ){
        let { activeUsers } = toJS(store),
        index = activeUsers.map(a=>a['user_ID']).indexOf(e['user_ID']);
        activeUsers.splice( index, 1 );
        store['activeUsers'] = activeUsers;
    }
    joinedUsers( e ){
        let { activeUsers } = store;
        activeUsers.push( e );
        store.activeUsers = activeUsers;
    }
}

export default observer( MainApp );