import React from 'react';
import { observer } from 'mobx-react';
import store from '../store/store';

class RoomTitle extends React.Component{
    render(){
        let { roomInfo } = store,
            { room_name } = roomInfo;
        return(
            <div className="top-bar">
                <button className="noti"></button>
                <div className="chat-name">
                    <span>{ room_name }</span>
                </div>
                <button className="search"></button>
            </div>
        )
    }
}

export default observer( RoomTitle );