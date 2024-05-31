//Функция запроса
async function postReq(link, data) {
    return await fetch(link, {
        method: "POST",
        body: data,
    });
}
async function create_user(event){
    //Создаем
    if(event.target.matches('.create_user, .create_role')){
        event.preventDefault();
        const formData = new FormData(event.target);
        const value = Object.fromEntries(formData.entries());

        let database = '';
        if(event.target.matches('.create_user')) {
            database = 'users';
            value.role = formData.getAll("role");
        }
        else {
            database = 'roles';
        }

        let data ={
            data : JSON.stringify(value),
            action : 'create',
            db: database
        }
        let response = await postReq('/kinoafisha/core/handler.php', JSON.stringify(data));

    }
}
document.addEventListener('submit', create_user);
async function update_user(event){
    //Удалить
    if(event.target.matches('.delete-button')){
        event.preventDefault();
        let table = event.target.closest("table");

        let database = '';
        if(table.matches('.users'))
            database = 'users';
        else
            database = 'roles';

        let data ={
            data : JSON.stringify({id: event.target.getAttribute('data-id')}),
            action : 'delete',
            db: database
        }
        let response = await postReq('/kinoafisha/core/handler.php', JSON.stringify(data));
        /*if(response.ok) window.location.reload();
        else alert('Internal Error');*/
    }
    //Обновить
    else if (event.target.matches('.update-button')){
        if (event.target.matches('.editing')) {
            let row = event.target.closest('tr');
            let id = row.cells[0].getAttribute('data-id');
            let type = event.target.closest('table').className; //какую таблицу обновляем
            //Собираем данные
            let values = {};
            if(type === 'users') {
                let selected = row.cells[2].querySelectorAll('option:checked');
                const options = Array.from(selected).map(el => el.value);
                values = {
                    name: row.cells[1].querySelector("input").value,
                    role: options,
                    id: id
                }
            }
            else{
                values = {
                    role: row.cells[1].querySelector("input").value,
                    id: id
                }
            }

            let data ={
                data : JSON.stringify(values),
                action : 'update',
                db: type
            }
            let response = await postReq('/kinoafisha/core/handler.php', JSON.stringify(data));
        }
        //Кнопка update для изменений таблицы у пользователя
        else {
            event.target.classList.add('editing');
            let type = event.target.closest('table').className;
            let row = event.target.closest('tr').getElementsByTagName("th");
            row[1].innerHTML = '<input type="text" class="form-control" name="name" required="">';
            //Получаем список ролей
            if(type === 'users') {
                let response = await postReq('/kinoafisha/core/include/getSelect.php');
                let text = await response.text();
                row[2].innerHTML = text;
            }
        }
    }
}
document.addEventListener('click', update_user);

//Кнопка регистрации
async function registration(event){
    if(event.target.matches('.registration_form')){
        event.preventDefault();
        const formData = new FormData(event.target);
        const value = Object.fromEntries(formData.entries());

        let data ={
            data : JSON.stringify(value)
        }
        let response = await postReq('/kinoafisha/core/registration.php', JSON.stringify(data));
        if(response.ok) window.location.href='/kinoafisha';
        else alert('Internal Error');
    }
}
document.addEventListener('submit', registration);