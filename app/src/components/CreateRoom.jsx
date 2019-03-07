import React from 'react';
import { observer } from 'mobx-react';
import ajax from '../functions/ajax';
import Variables from '../functions/variablesCall';
import store from '../store/store';
import history from '../functions/history';

class CreateRoom extends React.Component{
    constructor(){
        super();
        this.state = { username: '', room_name: '' };
        this.handleChange = this.handleChange.bind(this);
        this.submit = this.submit.bind(this);
    }
    componentDidMount(){
        let { app_name, create_room } = Variables['layout'];
        document.title = `${ app_name } | ${ create_room }`;
    }
    render(){
        let { username, room_name } = this.state;
        return(
            <div>
                <input 
                    name="username" 
                    placeholder="Username..."
                    type="text" 
                    value={ username }
                    onChange={this.handleChange} 
                />
                <input 
                    name="room_name" 
                    placeholder="Room Name..."
                    type="text" 
                    value={ room_name }
                    onChange={this.handleChange} 
                />
                <button
                    onClick={ this.submit }
                >
                    Submit
                </button>
                <br />
                <button onClick={()=>history.push('/')}>Back to HOME</button>
            </div>
        )
    }
    handleChange(e){
        let { name, value } = e.target;
        this.setState({ [name]: value });
    }
    submit(){
        let { host, paths } = Variables,
            { rooms_path } = paths,
            { username, room_name } = this.state,
            body = {
                "room_name": room_name,
                "username": username,
                "options": { "access": "private", "tags": "games" }
            };
        new ajax(`${host}${rooms_path}`, 'POST', {
            body,
            handle: this.handleResponse
        });
    }
    handleResponse( type, e ){
        console.log( e );
        if( type==='response' && !Object.keys(e).includes('error') ){
            store['roomInfo'] = e;
            store['activeUsers'] = e['users'];
            history.push(`/room/${ e['room_ID'] }`);
        }
    }
}

export default observer( CreateRoom );