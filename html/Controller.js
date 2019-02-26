class Controller {

    start(){
        this.messages = null;
        if (THE_USER){
            $('#navbarDropdown').html('Hi, ' + THE_USER.name);
            $('#loginOptions').html(`<a class="dropdown-item" href="${APP_PATH}/logout">Log out</a>`);
            let $user =  $('.authUname');
            let $umail = $('.authUmail');
            $user.val(THE_USER.name);
            $umail.val(THE_USER.email);
            $user.attr('disabled', true);
            $umail.attr('disabled', true);
        }
        this.query();
    }
    query(){
        let href = APP_PATH + '/json';
        let hash = Controller.getPageHash();
        if (hash){
            href += '/?' + hash;
        }
        fetch(href, {cache : "no-store"})
            .then(response => response.json())
            .then(data => {
                this.messages = data.messages.reverse();
                TOTAL_ENTRIES = data.total;
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

    static renderPage(){
        let contr = new Controller();
        contr.start();
    }

    static getPageHash(){
        let hash = location.hash;
        if (hash){
            return hash.substr(1);
        }
        return false;
    }

    static shiftPage(page){

        location.hash = 'p=' + page;
        Controller.renderPage();
    }

    static renderMessage (message){

        let deleteCard = '';
        if (THE_USER){
            if (THE_USER.id == message.user_id){
                deleteCard = `<a class="card-link" style="cursor:pointer; color:red" onclick="Controller.deleteEntry('${message.id}')">Delete entry</a>`;
            }
        }
        return `<div class="card" style="width: 100%; margin: 10px 0px;">
                      <div class="card-body">
                        <h5 class="card-title">${message.name}</h5>
                        <h6 class="card-subtitle mb2 text-muted">${message.email}</h6>
                        <p class="card-text">${message.html}</p>
                        ${deleteCard}
                       </div>
                     <div>`;

    }

    static appendMessage(html, $mBlock){
        let count = $mBlock.children().length;
        if (count == 10){
            $mBlock.children().last().remove();
        }
        $mBlock.prepend(html);
        Controller.showPagination();

    }

    static postMyMessage(){
        Controller.removeAlerts();
        fetch(APP_PATH + '/data', {method: 'post',
            headers: {
                "Content-type": "application/x-www-form-urlencoded; charset=UTF-8"
            },
            credentials: 'include',
            body: $('#new_message').serialize()})
            .then(response => response.json())
            .then(data => {
                if (data.errors){
                    data.errors.forEach(error => {
                        $('#new_message').find('input, textarea').each((n, obj) => {
                            if ($(obj).attr('name') == error.tgt){
                                $(obj).addClass('is-invalid');
                                const $parent = $(obj).parent();
                                $parent.append(`<div class="invalid-feedback">${error.msg}</div>`);
                            }
                        })
                    });
                }
                if (data.ok){
                    Controller.shiftPage(1);
                    Controller.appendMessage(Controller.renderMessage(data.ok), $('#message-block'));
                    document.getElementById('new_message').reset();
                    if (data.total) TOTAL_ENTRIES = data.total;
                    else TOTAL_ENTRIES++;

                    Controller.showPagination();
                } else if (data.fail){
                    alert(data.fail);
                }

            })
            .catch(error => {console.log(error.message); alert('Some error has occured'); });
    }

    static detectMaxPages(){
        let pages = Math.ceil(TOTAL_ENTRIES / 10);
        if (pages < 1) pages = 1;
        return pages;
    }

    static showPagination(){
        let pages = Controller.detectMaxPages();
        let current = Controller.detectCurrentPage();
        let pagination = '<nav aria-label="Page navigation"><ul class="pagination justify-content-center">';
        pagination += '<li class="page-item"><a class="page-link" onclick="Controller.prevPage()">Previous</a></li>';

        let c = 0;
        do {
            ++c;
            let style = 'page-item';
            if (c == current) style += ' active';
            pagination += `<li class="${style}"><a class="page-link" onclick="Controller.shiftPage(${c})">${c}</a></li>`;
        } while(c < pages);
        pagination += '<li class="page-item"><a class="page-link" onclick="Controller.nextPage()">Next</a></li>';
        pagination += '</ul></nav>';
        $('#nav_top').html(pagination);
        $('#nav_bottom').html(pagination);
    }

    static detectCurrentPage(){
        let hash = Controller.getPageHash();
        let page;
        if (hash){
            page = parseInt(hash.substr(2));
        } else
            page = 1;
        return page;
    }

    static prevPage(){
        let page = Controller.detectCurrentPage();
        if (page && page != 1){
            Controller.shiftPage(page - 1);
        }
    }
    static nextPage(){
        let page = Controller.detectCurrentPage();
        let max = Controller.detectMaxPages();
        if (page && page < max){
            Controller.shiftPage(page + 1);
        }
    }

    static loginAttempt(){
        Controller.removeAlerts();
        fetch(APP_PATH + '/login', {method: 'post',
            redirect : 'follow',
            headers: {
                "Content-type": "application/x-www-form-urlencoded; charset=UTF-8"
            },
            credentials: 'include',
            body: $('#loginForm').serialize()})
            .then(response => {
                if (!response.redirected){
                    return response.json().then(data => {
                        if (data.errors){
                            data.errors.forEach(error => {
                                $('#loginForm').find('input').each((n, obj) => {
                                    if ($(obj).attr('name') == error.tgt){
                                        $(obj).addClass('is-invalid');
                                        const $parent = $(obj).parent();
                                        $parent.append(`<div class="invalid-feedback">${error.msg}</div>`);
                                    }
                                })
                            });
                        }
                        if (data.user){
                            $('#loginModal').modal('hide');
                            Controller.removeAlerts();
                            Controller.homePage();
                        }
                    })
                } else {
                    $('#loginModal').modal('hide');
                    window.location.href = response.url;
                }
            })
            .catch(error => {console.log(error.message); alert('Some error has occured'); });
    }


    static addUser(){
        Controller.removeAlerts();
        fetch(APP_PATH + '/auth', {method: 'post',
            headers: {
                "Content-type": "application/x-www-form-urlencoded; charset=UTF-8"
            },
            credentials: 'include',
            body: $('#newuserForm').serialize()})
            .then(response => response.json())
            .then(data => {
                if (data.errors){
                    data.errors.forEach(error => {
                        $('#newuserForm').find('input').each((n, obj) => {
                            if ($(obj).attr('name') == error.tgt){
                                $(obj).addClass('is-invalid');
                                const $parent = $(obj).parent();
                                $parent.append(`<div class="invalid-feedback">${error.msg}</div>`);
                            }
                        })
                    });
                }
                if (data.ok){
                    $('#newuserModal').modal('hide')
                    Controller.removeAlerts();
                    Controller.updateCurrentPage();
                } else if (data.fail){
                    alert(data.fail);
                }
            })
            .catch(error => {console.log(error.message); alert('Some error has occured'); });
    }

    static updateCurrentPage(){
        let page = Controller.detectCurrentPage();
        Controller.shiftPage(page);
    }

    static homePage(){
        if (APP_PATH !== '')
            window.location = APP_PATH;
        else window.location = '/';
    }


    static removeAlerts(){
        $('input').removeClass('is-invalid');
        $('.invalid-feedback').remove();
    }

    static deleteEntry(mId){
        if (confirm('Delete this entry?')){
            fetch(APP_PATH + '/delete', {method: 'post',
                headers: {
                    "Content-type": "application/x-www-form-urlencoded; charset=UTF-8"
                },
                credentials: 'include',
                body: `message_id=${mId}`})
                .then(response => response.json())
                .then(data => {
                    if (data.error){
                        alert(data.error);
                    }
                    if (data.ok && data.total){
                        TOTAL_ENTRIES = data.total;
                        Controller.showPagination();
                        Controller.shiftPage(Controller.detectCurrentPage());
                    }
                })
                .catch(error => {console.log(error.message); alert('Some error has occured'); });
        }

    }
}