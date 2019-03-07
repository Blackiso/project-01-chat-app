import React from 'react';
import { observer } from 'mobx-react';
import store from '../store/store';
import { toJS } from 'mobx';
import Linkify from 'react-linkify';
let Entities = require('html-entities').XmlEntities,
    entities = new Entities();

class Message extends React.Component{
    render(){
        let { data } = this.props,
        { roomInfo } = toJS( store ),
        { user_ID:a } = roomInfo,
        { user_ID:b, message, username, admin } = data,
        icon = username.toUpperCase()[0],
        text = message,
        isAdmin = admin? ' admin-msg':'',
        myMessage = a===b? ' me': '';

        return(
            <div className={ "message-box" + myMessage + isAdmin }>
                <div className="icon">{ icon }</div>
                <div className="message">
                    <Linkify properties={{target: '_blank', style: {color: '#fff', fontWeight: 'bold'}}}>
                        { entities.decode( text ) }
                    </Linkify>
                </div>
            </div>
        )
    }
}

export default observer( Message );