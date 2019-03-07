import React from 'react';
import { observer } from 'mobx-react';
import Variables from '../functions/variablesCall';
import ajax from '../functions/ajax';
import store from '../store/store';

class MessageInput extends React.Component{
    constructor(){
        super();
        this.state = { message: '' };
        this.handleChange = this.handleChange.bind(this);
        this.handleSubmit = this.handleSubmit.bind(this);
    }
    render(){
        let { layout } = Variables,
            { type_message } = layout,
            { message } = this.state;
        return(
            <form className="bottom-bar" onSubmit={this.handleSubmit}>
                <div className="emoji">
                    <i className="fas fa-smile-beam" />
                </div>
                <input 
                    autoCorrect="off"
                    autoComplete="off"
                    type="text" 
                    name="message" 
                    value={message} 
                    placeholder={type_message} 
                    onChange={this.handleChange} 
                />
                <div className="send" type="submit">
                    <i className="fas fa-paper-plane" />
                </div>
            </form>
        )
    }
    handleChange(e){
        let { value, name } = e.target;
        this.setState({ [name]: value });
    }
    handleSubmit(e){
        e.preventDefault();
        let { host, paths } = Variables,
            { messages_path } = paths,
            { roomInfo } = store,
            { room_ID } = roomInfo,
            { message } = this.state;
            this.setState({ message: '' });

        new ajax(`${host}${messages_path}${room_ID}`,'POST',{
                body: { message },
                handle: this.response
            });
    }
    response( type, e ){
        console.log( type, e );
        if( type === 'response' ){
            let { messages, socket, activeUsers } = store,
                msg = {
                    "type": "msg",
                    "body": {
                        "msg_ID": e['id']
                    }
                };
            socket.send( JSON.stringify(msg) );

            messages.push(e);
            store['messages'] = messages;
        }
    }
}

export default observer( MessageInput );