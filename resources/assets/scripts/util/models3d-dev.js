let xmlhttps = window.XMLHttpRequest ? new XMLHttpRequest() : new ActiveXObject("Microsoft.XMLHTTP");

// Guardando objetos 3d
export const makeModel3d = (e)=>{
    e.preventDefault();

    let obj = {
        formData : new FormData(e.target),
        form: e.target,
        config : {}
    };

    let fields = obj.formData.entries();

    for (var [key, value] of fields) {
        obj.config[key] = value;
    }

    obj.config['modelo_3d_archivo'] = obj.config['modelo_3d_archivo'].name;

    new Promise((resolver, rechazar)=>{
        let xmlhttp = xmlhttps, 
            url = `${window.location.origin}/wp/wp-admin/admin-ajax.php`;

        xmlhttp.onreadystatechange = function() {
            if (this.readyState === 4 && this.status === 200) {
                resolver(JSON.parse(this.responseText));
            }
        }

        xmlhttp.open('POST' , url ,true);
        let file = obj.form.querySelector('[name="modelo_3d_archivo"]').files[0];

        obj.formData.append('action', 'modelos_3d');
        obj.formData.append('file', file)
        obj.formData.append('config', JSON.stringify(obj.config))

        xmlhttp.send(obj.formData);
    })
}

//document.querySelector('#models3dform').addEventListener('submit', makeModel3d, false);

export const ajax3d = (obj)=>{
    return new Promise((resolver, rechazar)=>{
        let xmlhttp = xmlhttps;
        xmlhttp.onreadystatechange = function() {
            if (this.readyState === 4 && this.status === 200) {
                resolver(JSON.parse(this.responseText));
            }
        }

        xmlhttp.open('get' , `${window.location.origin}/wp/wp-admin/admin-ajax.php` ,true);
        xmlhttp.setRequestHeader("Content-type", "application/json");
        xmlhttp.send();
    })
}