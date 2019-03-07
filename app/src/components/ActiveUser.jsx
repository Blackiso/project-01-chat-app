import React from 'react';
import { observer } from 'mobx-react';

class ActiveUser extends React.Component{
    render(){
        let { data } = this.props,
        { username, admin } = data,
        icon = username.toUpperCase()[0],
        isAdmin = admin? ' admin-user': '';
        return(
            <div className={ "user" + isAdmin }>
                <div className="icon">{ icon }</div>
                <span className="username">{ username }</span>
                <i className="fas fa-circle"></i>
            </div>
        )
    }
}

export default observer( ActiveUser );