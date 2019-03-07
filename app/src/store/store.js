import { extendObservable } from 'mobx';

class Store {
    constructor(){
        extendObservable(this, {
            activeUsers : [],
            messages : [],
            roomInfo: {},
            socket: null
        });
    }
}

export default new Store();