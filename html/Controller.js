class Controller {
    constructor(hrefPath){
        this.href = hrefPath;
    }
    start(){
        console.log('start');
        this.messages = null;
        this.query();
    }
    query(){
        fetch(this.href + '/json', {cache : "no-store"})
            .then(response => response.json())
            .then(data => {
                this.messages = data;
                this.display();
            })
            .catch(error => {console.log(error.message); alert('Some error has occured'); });
    }

    display(){
        const $mBlock = $('#message-block');
        $mBlock.html('');
        this.messages.forEach(message => {

            Controller.appendMessage(Controller.renderMessage(message), $mBlock);

        });

    }

    static renderMessage (message){

        return `<div class="card" style="width: 100%; margin: 10px 0px;">
                      <div class="card-body">
                        <h5 class="card-title">${message.name}</h5>
                        <h6 class="card-subtitle mb2 text-muted">${message.email}</h6>
                        <p class="card-text">${message.html}</p>
                       </div>
                     <div>`;

    }

    static appendMessage(html, $mBlock){
        let count = $mBlock.children().length;
        if (count == 10){
            $mBlock.children().last().remove();
        }
        $mBlock.prepend(html);

    }
}

$(function () {
    let contr = new Controller('/medcom');
    contr.start();
});
function postMyMessage (){
    fetch('/medcom/data', {method: 'post',
                           headers: {
                                "Content-type": "application/x-www-form-urlencoded; charset=UTF-8"
                           },
                           credentials: 'include',
                           body: $('#new_message').serialize()})
        .then(response => response.json())
        .then(data => {
            console.log(data);
        })
        .catch(error => {console.log(error.message); alert('Some error has occured'); });
}