function addEventListeners() {
    window.addEventListener('load', function () {
        // Load events from the API on page load
        loadMoreEvents();
    });
}

function encodeForAjax(data) {
    if (data == null) return null;
    return Object.keys(data).map(function (k) {
        return encodeURIComponent(k) + '=' + encodeURIComponent(data[k])
    }).join('&');
}

function sendAjaxRequest(method, url, data, handler) {
    let request = new XMLHttpRequest();

    request.open(method, url, true);
    request.setRequestHeader('X-CSRF-TOKEN', document.querySelector('meta[name="csrf-token"]').content);
    request.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    request.addEventListener('load', handler);
    request.send(encodeForAjax(data));
}

// Infinite scroll
let page = 1;

function loadMoreEvents() {
    const xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function () {
        if (xhr.readyState === 4) {
            if (xhr.status === 200) {
                const response = JSON.parse(xhr.responseText);
                const events = response.events.data;

                const eventsContainer = document.getElementById('eventsContainer');
                if (eventsContainer) {
                    events.forEach(event => {
                        const eventCard = document.createElement('div');
                        eventCard.classList.add('event-card');

                        eventCard.innerHTML = `
                            <a href="/event/${event.id}">
                                <img src="/images/event_default.png" alt="Event Image" class="event-image">
                                <div class="event-info">
                                    <h3>${event.name}</h3>
                                    <p>${event.description}</p>
                                    <p>${event.eventdate}</p>
                                </div>
                            </a>
                        `;
                        eventsContainer.appendChild(eventCard);
                    });

                    page++;

                    if (response.events.next_page_url) {
                        window.addEventListener('scroll', scrollHandler);
                    }
                }
            } else {
                console.error('Error fetching events:', xhr.status, xhr.statusText);
            }
        }
    };

    const url = `/api/events-ajax?page=${page}`;
    xhr.open('GET', url, true);
    xhr.send();
}

function scrollHandler() {
    const threshold = 200;

    if (window.innerHeight + window.scrollY >= document.body.offsetHeight - threshold) {
        window.removeEventListener('scroll', scrollHandler);
        loadMoreEvents();
    }
}

function openOptions() {
    const options = document.querySelectorAll(".event-manage");
    let topElement;
    if (options) {
        options.forEach((option) => {
            option.addEventListener("click", (e) => {
                const alreadyOpen = document.querySelector(".event-manage-div");
                if (!alreadyOpen) {
                    const id_event = option.parentElement.id.split("-")[1];
                    let pinButtonText = "Pin";
                    let hideButtonText = "Hide";
                    let pinAction = true;
                    let hideAction = true;
                    let selectedEvents;
                    let events;

                    if (option.parentElement.parentElement.classList.contains("created-events-container")) {
                        selectedEvents = ".created-events-container";
                        events = 'created';
                    }
                    else {
                        selectedEvents = ".joined-events-container";
                        events = 'joined';
                    }

                    const isEventPinned = option.parentElement.firstElementChild.firstElementChild.classList.contains("event-pin");
                    const isEventHidden = option.parentElement.firstElementChild.firstElementChild.classList.contains("event-hidden");

                    if (isEventPinned) {
                        pinButtonText = "Unpin";
                        pinAction = false;
                    }
                    if (isEventHidden) {
                        hideButtonText = "Unhide";
                        hideAction = false;
                    }

                    const optionsDiv = document.createElement("div");
                    optionsDiv.classList.add("event-manage-div");
                    optionsDiv.style.top = (parseInt(e.clientY) + parseInt(window.scrollY)).toString() + "px";
                    optionsDiv.style.left = (parseInt(e.clientX) + parseInt(window.scrollX) - 100).toString() + "px";

                    const pinButton = document.createElement("button");
                    pinButton.type = "button";
                    pinButton.textContent = pinButtonText;
                    pinButton.addEventListener("click", () => {
                        topElement = option.parentElement;
                        if (pinAction) {
                            if (isEventHidden) {
                                option.parentElement.firstElementChild.firstElementChild.remove();
                            }

                            const pin = document.createElement("img");
                            pin.src = "/icons/pin.png";
                            pin.alt = "Pin Icon";
                            pin.classList.add("event-pin");
                            option.parentElement.firstElementChild.prepend(pin);
                            option.parentElement.remove();
                            document.querySelector(selectedEvents).prepend(topElement);
                        }
                        else {
                            option.parentElement.firstElementChild.firstElementChild.remove();
                            const findFirstHidden = document.querySelector(`${selectedEvents} .event-hidden`);
                            if (findFirstHidden) {
                                console.log(findFirstHidden.parentElement.parentElement);
                                findFirstHidden.parentElement.parentElement.parentElement.insertBefore(topElement, findFirstHidden.parentElement.parentElement.previousSibling);
                            }
                            else {
                                document.querySelector(selectedEvents).appendChild(topElement);
                            }
                        }
                        sendAjaxRequest('PUT', `/api/user/manage-event/${id_event}`, { events: events, actionName: 'pin', pinAction: pinAction }, function () { });
                    });

                    const hideButton = document.createElement("button");
                    hideButton.type = "button";
                    hideButton.textContent = hideButtonText;
                    hideButton.addEventListener("click", () => {
                        topElement = option.parentElement;
                        if (hideAction) {
                            if (isEventPinned) {
                                option.parentElement.firstElementChild.firstElementChild.remove();
                            }

                            const hide = document.createElement("p");
                            hide.textContent = "Hidden";
                            hide.classList.add("event-hidden");
                            option.parentElement.firstElementChild.prepend(hide);
                            option.parentElement.remove();
                            document.querySelector(selectedEvents).appendChild(topElement);
                        }
                        else {
                            option.parentElement.firstElementChild.firstElementChild.remove();
                            const findLastPinned = document.querySelectorAll(`${selectedEvents} .event-pin`)[document.querySelectorAll(`${selectedEvents} .event-pin`).length - 1];
                            if (findLastPinned) {
                                findLastPinned.parentElement.parentElement.parentElement.insertBefore(topElement, findLastPinned.parentElement.parentElement.nextSibling);
                            }
                            else {
                                document.querySelector(selectedEvents).prepend(topElement);
                            }
                        }
                        sendAjaxRequest('PUT', `/api/user/manage-event/${id_event}`, { events: events, actionName: 'hide', hideAction: hideAction }, function () { });
                    });

                    optionsDiv.appendChild(pinButton);
                    optionsDiv.appendChild(hideButton);

                    option.parentElement.appendChild(optionsDiv);
                }
            });
        });
    }
}

function closeOptions() {
    document.addEventListener("click", (e) => {
        if (e.target.classList.contains("event-manage")) {
            return;
        }
        const options = document.querySelectorAll(".event-manage-div");

        options.forEach((option) => {
            option.remove();
        });
    });
}

function switchEvents() {
    const createdEvents = document.querySelector(".created-events-container");
    const joinedEvents = document.querySelector(".joined-events-container");
    const createdEventsButton = document.querySelector(".created-events-title");
    const joinedEventsButton = document.querySelector(".joined-events-title");

    if (createdEvents && joinedEvents && createdEventsButton && joinedEventsButton) {
        createdEventsButton.addEventListener("click", () => {
            createdEvents.style.display = "flex";
            joinedEvents.style.display = "none";
            createdEventsButton.classList.add("active");
            joinedEventsButton.classList.remove("active");
        });

        joinedEventsButton.addEventListener("click", () => {
            createdEvents.style.display = "none";
            joinedEvents.style.display = "flex";
            createdEventsButton.classList.remove("active");
            joinedEventsButton.classList.add("active");
        });
    }

}

function closeSureOptions() {
    document.addEventListener("click", (e) => {
        if (e.target.classList.contains("fake") || e.target.classList.contains("yes")) {
            return;
        }
        const sureboxes = document.querySelectorAll(".surebox");
        sureboxes.forEach((surebox) => {
            surebox.remove();
        });
    });

}

function removeParticipant() {
    const fakebuttons = document.querySelectorAll(".fake.button.remove");
    fakebuttons.forEach((fakebutton) => {
        fakebutton.addEventListener("click", () => {
            const participant_id = fakebutton.id;
            const participant_card = document.getElementById(participant_id);
            const sureboxExists = participant_card.querySelector(".surebox");

            if (!sureboxExists) {
                const surebox = document.createElement("div");
                surebox.classList.add("surebox");
                surebox.innerHTML = `
          <p>Are you sure ?</p>
          <div class="surebox-buttons">
          <button type="submit" class="surebox button yes">Yes</button>
          <button type="button" class="surebox button no">No</button>
          </div>
      `;
                fakebutton.parentElement.appendChild(surebox);
                const noButton = surebox.querySelector(".surebox.button.no");
                noButton.addEventListener("click", () => {
                    surebox.remove();
                });

            }

        })
    });
    closeSureOptions();
}




addEventListeners();
openOptions();
closeOptions();
switchEvents();
removeParticipant();
