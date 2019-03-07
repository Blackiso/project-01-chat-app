import React, { Component } from 'react';
import { observer } from 'mobx-react';
import './style/App.css';
import { Router, Route } from 'react-router-dom';
import history from './functions/history';
import JoinRoom from './components/JoinRoom';
import CreateRoom from './components/CreateRoom';
import MainApp from './components/MainApp';
import Example from './components/Example';

class App extends Component {
  render() {
    return (
      <Router history={history} >
        <React.Fragment>
          <Route component={ CreateRoom } path="/create_room" exact />
          <Route component={ JoinRoom } path="/join_room/:room_ID" exact />
          <Route component={ JoinRoom } path="/join_room/" exact />
          <Route component={ MainApp } path="/room/:room_ID" exact />
          <Route component={ MainApp } path="/room/" exact />
          <Route component={ Example } path="/" exact />
        </React.Fragment>
      </Router>
    );
  }
}

export default observer( App );