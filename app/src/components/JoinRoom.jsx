import React from 'react';
import { observer } from 'mobx-react';
import ajax from '../functions/ajax';
import Variables from '../functions/variablesCall';
import store from '../store/store';
import history from '../functions/history';

class JoinRoom extends React.Component{
    constructor(){
        super();
        this.state = { username: '', room_ID: '' };
        this.handleChange = this.handleChange.bind(this);
        this.submit = this.submit.bind(this);
    }
    componentDidMount(){
        let { room_ID } = this.props['match']['params'],
        { app_name, join_room } = Variables['layout'];

        document.title = `${ app_name } | ${ join_room }`;
        if( room_ID ) this.setState({ room_ID });
    }
    render(){
        let { username, room_ID } = this.state;
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
                    name="room_ID" 
                    placeholder="Room ID..."
                    type="text" 
                    value={ room_ID }
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
            { users_path } = paths,
            { username, room_ID } = this.state,
            body = {
                room_ID: room_ID,
                username,
                "options": { "access": "private", "tags": "games" }
            };
            console.log( username, room_ID );
        new ajax(`${host}${users_path}`, 'POST', {
            body,
            handle: this.handleResponse
        });
    }
    handleResponse( type, e ){
        console.log(e);
        store['roomInfo'] = e;
        store['activeUsers'] = e['users'];
        if(e['room_ID']) history.push(`/room/${e['room_ID']}`);
    }
}

export default observer( JoinRoom );