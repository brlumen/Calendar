document.addEventListener("DOMContentLoaded", function() {
    // Инициализация переменных
    var emptySlots = document.querySelectorAll(".calendar-event-empty.clickable");
    var dayHeaders = document.querySelectorAll(".calendar-day-header.clickable");
    var createEventModal = document.getElementById("createEventModal");
    var eventModal = document.getElementById("eventModal");
    var selectedDate = null;
    var activeSlot = null;
    var activeHeader = null;
    
    // Обработка клика по кнопке "больше событий"
    var moreEventButtons = document.querySelectorAll(".calendar-more-events");
    moreEventButtons.forEach(function(button) {
        button.addEventListener("click", function() {
            var date = this.getAttribute("data-date");
            var events = JSON.parse(this.getAttribute("data-events"));
            showDayEvents(date, events);
        });
    });

    // Функция для показа событий дня
    function showDayEvents(date, events) {
        var modalTitle = document.getElementById("modalTitle");
        var modalEventList = document.getElementById("modalEventList");
        var span = document.getElementsByClassName("close")[0];
        
        var eventsForDateText = eventModal.getAttribute("data-events-for-date-text");
        modalTitle.innerHTML = eventsForDateText.replace("%s", date);
        
        var eventHtml = "";
        if(events !== null){
            events.forEach(function(event) {
                eventHtml += "<a href='" + event.url + "' class='modal-event'>";
                eventHtml += "<div class='event-time'>" + event.time + "</div>";
                eventHtml += "<div class='event-duration'>" + event.duration + "</div>";
                eventHtml += "<div class='event-name'>" + event.name + "</div>";
                eventHtml += "<div class='event-project'>" + event.project_name + "</div>";
                eventHtml += "</a>";
            });
        }
    
        modalEventList.innerHTML = eventHtml;
        eventModal.style.display = "block";
        
        var createEventForm = document.getElementById("createEventForm");
        
        // Добавляем обработчик для кнопки создания события
        var createEventBtn = eventModal.querySelector('#create-event-btn');
        if (createEventBtn) {
            createEventBtn.date = date;
            createEventBtn.addEventListener('click', showCreateEventForm);
        }
        
        span.onclick = function() {
            eventModal.style.display = "none";
            createEventForm.style.display = "none";
            createEventBtn.removeEventListener('click', showCreateEventForm);
        };
        
        window.onclick = function(event) {
            if (event.target == eventModal) {
                eventModal.style.display = "none";
                createEventForm.style.display = "none";
                createEventBtn.removeEventListener('click', showCreateEventForm);
            }
        };
    }

    // Функция для показа формы создания события
    function showCreateEventForm(e) {
        var createEventForm = document.getElementById("createEventForm");
        
        // Если форма уже отображается, скрываем её
        if (createEventForm.style.display === "block") {
            createEventForm.style.display = "none";
            return;
        }
        
        selectedDate = e.currentTarget.date;
        
        // Очищаем форму
        document.getElementById("eventName").value = "";
        
        // Отображаем выбранную дату
        // Дата приходит в формате DD.MM.YYYY, просто отображаем её как есть
        document.getElementById("selectedDate").textContent = selectedDate;
        
        // Устанавливаем значения времени по умолчанию
        var timeStart = document.getElementById("eventTimeStart");
        var timeEnd = document.getElementById("eventTimeEnd");
        
        // Находим опцию для 9:00 и 10:00
//        for (var i = 0; i < timeStart.options.length; i++) {
//            if (timeStart.options[i].value === "09:00") {
//                timeStart.selectedIndex = i;
//            }
//            if (timeEnd.options[i].value === "10:00") {
//                timeEnd.selectedIndex = i;
//            }
//        }
        
        // Показываем форму создания события
        createEventForm.style.display = "block";
    }

    // Обработка клика по кнопке создания события
    var createEventBtn = document.getElementById("createEventBtn");
    if (createEventBtn) {
        createEventBtn.addEventListener("click", function() {
            var eventName = document.getElementById("eventName").value;
            var timeStart = document.getElementById("eventTimeStart").value;
            var timeEnd = document.getElementById("eventTimeEnd").value;
            
            if (!eventName) {
                alert("Пожалуйста, введите название события");
                return;
            }
            
            if (!timeStart || !timeEnd) {
                alert("Пожалуйста, укажите время начала и окончания события");
                return;
            }
            
            // Формируем URL с параметрами
            var url = "plugin.php?page=Calendar/event_add_page" + 
                     "&name=" + encodeURIComponent(eventName) +
                     "&date=" + selectedDate +
                     "&time_start=" + encodeURIComponent(timeStart) +
                     "&time_end=" + encodeURIComponent(timeEnd);
            
            // Переходим на страницу создания события
            window.location.href = url;
        });
    }
    
    // Обработка клика по пустому слоту
    emptySlots.forEach(function(slot) {
        slot.addEventListener("click", function() {
            // Если кликнули по уже активному слоту - показываем модальное окно
            if (activeSlot === this) {
                var date = this.getAttribute("data-date");
                var events = JSON.parse(this.getAttribute("data-events"));
                showDayEvents(date, events);
//                showCreateEventForm(this.getAttribute("data-date"));
                activeSlot.classList.remove("active");
                activeSlot = null;
            } else {
                // Убираем выделение с предыдущего активного слота и заголовка
                if (activeSlot) {
                    activeSlot.classList.remove("active");
                }
                if (activeHeader) {
                    activeHeader.classList.remove("active");
                    activeHeader = null;
                }
                
                // Выделяем текущий слот
                this.classList.add("active");
                activeSlot = this;
            }
        });
    });
    
    // Обработка клика по заголовку дня
    dayHeaders.forEach(function(header) {
        header.addEventListener("click", function() {
            // Если кликнули по уже активному заголовку - показываем модальное окно
            if (activeHeader === this) {
                showCreateEventForm(this.getAttribute("data-date"));
                activeHeader.classList.remove("active");
                activeHeader = null;
            } else {
                // Убираем выделение с предыдущего активного заголовка и слота
                if (activeHeader) {
                    activeHeader.classList.remove("active");
                }
                if (activeSlot) {
                    activeSlot.classList.remove("active");
                    activeSlot = null;
                }
                
                // Выделяем текущий заголовок
                this.classList.add("active");
                activeHeader = this;
            }
        });
    });

    // Обработка кликов по событиям
    let activeEvent = null;

    // Обработка событий в календаре
    document.querySelectorAll('.calendar-cell .calendar-event').forEach(event => {
        event.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Проверяем, является ли событие ссылкой (для модального окна)
            if (this.tagName === 'A') {
                window.location.href = this.getAttribute('href');
                return;
            }
            
            // Для событий в календаре
            const link = this.querySelector('a');
            if (!link) return;
            
            const href = link.getAttribute('href');
            if (activeEvent === this) {
                // Второй клик - переходим по ссылке без снятия выделения
                window.location.href = href;
            } else {
                // Первый клик - выделяем событие
                if (activeEvent) {
                    activeEvent.classList.remove('active');
                }
                this.classList.add('active');
                activeEvent = this;
            }
        });
    });

    // Снимаем выделение при клике вне события
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.calendar-event') && activeEvent) {
            activeEvent.classList.remove('active');
            activeEvent = null;
        }
    });
}); 