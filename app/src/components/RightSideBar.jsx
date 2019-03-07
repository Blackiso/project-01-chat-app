import React from 'react';
import { observer } from 'mobx-react';
import ActiveUsersList from './ActiveUsersList';
import store from '../store/store';
import renameUsers from '../functions/renameUsers';
import { toJS } from 'mobx';
import checkArray from '../functions/checkArray';

class RightSideBar extends React.Component{
    render(){
        let { roomInfo, activeUsers } = toJS(store),
        { user_ID } = roomInfo,
        users = renameUsers( activeUsers ),
        username = ()=>{
            let user = users.filter(a=>a['user_ID']===user_ID);
            return checkArray(user, [0, 'username']) || '';
        };
        return(
            <div className="right-bar">
                <div className="admin">
                    <div className="icon">
                        <i className="fas fa-crown"></i>
                    </div>
                    <span className="admin-username">
                        { username() }
                    </span>
                </div>
                <ActiveUsersList />
            </div>
        )
    }
}

export default observer( RightSideBar );