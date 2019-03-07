function renameUsers(ar){
    return ar.map(e=>{
        let fi = ar.filter(u=>u['username']===e['username']);
        if( fi.length > 1 ){
            fi.forEach((f,j)=>{
                f['username'] = f['username']+(j?'_'+j:'');
                return f;
            });
            return e;
        }else return e;
    });
}
export default renameUsers;