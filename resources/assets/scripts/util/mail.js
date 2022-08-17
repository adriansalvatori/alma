export const mailer = (obj, sending) => {
    let form = document.querySelector(obj.formElement);
    let filesAllowed = {
        pdf: {
            size: 10,
        },
        png: {
            size: 10,
        },
        jpg: {
            size: 10,
        },
        jpeg: {
            size: 10,
        },
        docx: {
            size: 10,
        },
        doc: {
            size: 10,
        },
        xlsx: {
            size: 10,
        },
        xls: {
            size: 10,
        },
        ppt: {
            size: 10,
        },
        pptx: {
            size: 10,
        },
        txt: {
            size: 10,
        },
    }
    if(Array.isArray(obj['files'])) {
        obj['files'].forEach(el =>{
            filesAllowed[el.type.split(" ").join("").toLowerCase()] = {
                size: el.size * 1 || 10,
            };
        })
    }
    

    if(form) {
        // Agregamos un name al campo si no tiene ninguno asignado y tambien un identificador con su name
        let hasName = form.querySelectorAll(['input', 'textarea', 'select']),
            autoName = 0,
            mailBody;
        let submitTarget = form.querySelector('input[type="submit"]') || form.querySelector('button');
        hasName.forEach(element => {
            if(element.type !== 'submit') {
                if(!element.name){
                    element.name = `autoName_${autoName}`;
                    autoName++
                }
                element.dataset.formId = element.name;
            }
        });

        form.onsubmit = (e)=> {
            let xmlhttps = window.XMLHttpRequest ? new XMLHttpRequest() : new ActiveXObject("Microsoft.XMLHTTP");
            e.preventDefault()
            if(obj.spanBlock){
                submitTarget.style.pointerEvents = 'none';
            }
            let xmlhttp = xmlhttps;
            // Iniciamos el objeto que va guardar nuestra data
            let formData = new FormData();

            // inciamos un objeto con la data del formulario para separar los archivos de los campos comunes
            let fields = new FormData(form);
            let listFiles = [];

            xmlhttp.onreadystatechange = function() {
                if (this.readyState === 4 && this.status === 200) {
                    let reponse = {
                        campos: fields,
                        target : form,
                        trigger: submitTarget,
                        files : listFiles,
                        data: formData,
                        body: mailBody,
                        response: JSON.parse(this.responseText),
                    }
                    submitTarget.style.pointerEvents = 'auto';
                    sending(reponse);
                }
            }


            // Guardamos la data que necesitamos
            for (const key in obj) {
                if(key == "to"){
                    formData.append(key, JSON.stringify({mails : obj[key]}))
                }else {
                    formData.append(key, obj[key])
                }
            }

            
            let list = [];

            for (var [key, value] of fields.entries()) {
                // obtenemos el campo que esta en este momento activo
                let fieldName = document.querySelector(`[data-form-id="${key}"]`);
                let name = fieldName.dataset.name || fieldName.name;

                // Validamos si es un archivo o un campo
                if(value.name || value.name == ""){
                    if(value.name !== ""){
                        // Guardamos la extension y el tamaño para usarlo mas tarde
                        let fileType = value.name.split('.').pop().toLowerCase();
                        let fileSize = value.size/1024/1024;
                        let fileTemp = {
                            [value.name] : {
                                success: false,
                                size : '',
                                type : '',
                                allowed: filesAllowed,
                            },
                        }

                        let sizeDone = false;
                        // Comprobamos si el archivo esta permitido
                        if(filesAllowed[fileType]) {
                            if(filesAllowed[fileType].size >= fileSize) {
                                sizeDone = true;
                            }else {
                                sizeDone = false;
                            }

                            fileTemp[value.name].type = fileType;
                            fileTemp[value.name].size = fileSize;

                            if(sizeDone) {
                                fileTemp[value.name].success = true;
                                formData.append("fileToUpload[]", value);
                            }else {
                                fileTemp[value.name].success = false;
                            }
                        }

                        listFiles.push(fileTemp);
                    }
                }else {
                    // Creamos un html comun con el name y value del campo, luego lo insertamos en un array
                    list.push(`
                    <tr>
                        <td style="font-weight:bold; border: 1px solid black; border-collapse: collapse;">
                        ${name}
                        </td>
                        <td style="border: 1px solid black; border-collapse: collapse;">
                        ${value}
                        </td>
                    </tr>`)
                }
            }
            // Creamos el html que se enviara al correo y lo agregamos al objeto dataForm
            mailBody = `
            <table style="border: 1px solid black; border-collapse: collapse;">
                ${list.join("")}
            </table>`;
            formData.append('mailBody', mailBody)


            let url = `${window.location.origin}/wp/wp-admin/admin-ajax.php`;
            xmlhttp.open('POST' , url ,true);
            xmlhttp.send(formData);
        }
    }
}
