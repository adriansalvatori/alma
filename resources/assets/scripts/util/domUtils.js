
export const add_to_cart = (triggerBtn, added) => {
    let btn = document.querySelectorAll(triggerBtn);
    btn.forEach(el => {
        el.onclick = (e)=> {
            e.preventDefault()
            let xmlhttps = window.XMLHttpRequest ? new XMLHttpRequest() : new ActiveXObject("Microsoft.XMLHTTP");
            el.style.pointerEvents = 'none';
            el.classList.remove('is-done');
            el.classList.remove('is-fail');
            el.classList.add('is-loading');

            xmlhttps.onreadystatechange = function() {
                if (this.readyState === 4 && this.status === 200) {
                    let reponse = JSON.parse(this.responseText)
                    el.style.pointerEvents = 'auto';
                    
                    el.classList.remove('is-loading')
                    if(reponse.success) {
                        el.classList.add('is-done')
                    }else {
                        el.classList.add('is-fail')
                    }
                    added(reponse);
                }
            }
    
            let formData = new FormData();
    
            formData.append('action', 'add_to_cartinador')
            formData.append('id', el.dataset.id || 0)
            formData.append('quantity', el.dataset.quantity || 0)
    
            let url = `${window.location.origin}/wp/wp-admin/admin-ajax.php`;
            xmlhttps.open('POST' , url ,true);
            xmlhttps.send(formData);
        }
    })
}
