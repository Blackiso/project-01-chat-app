class ajax {
    constructor( url, type, obj ){
        this.xhr = new XMLHttpRequest();
        let _this = this,
        { body, handle } = obj;
        this.xhr.addEventListener('readystatechange', function(){
            if( this.readyState === 4 && this.status === 200 ){
                console.log(_this.xhr.responseText);
                let json = /* _this.xhr.responseText; */JSON.parse(_this.xhr.responseText);
                handle( 'response', json );
            }
        });
        this.xhr.addEventListener('error', e=>console.log('ERROR',e) );
        this.xhr.addEventListener('timeout', e=>handle( 'timeout', e ));
        this.xhr.addEventListener('abort', e=> console.log('aborted', e));
        this.xhr.open( type, url );
        this.xhr.setRequestHeader( 'Content-Type', 'application/json' );
        this.xhr.setRequestHeader( 'Access-Control-Allow-Credentials', 'include');
        this.xhr.withCredentials = true;
        this.xhr.send( JSON.stringify( body ) );

        return this;
    }
    abort(){
        this.xhr.abort();
        console.log('must be aborted!');
    }
}

export default ajax;