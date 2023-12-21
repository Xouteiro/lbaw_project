function addEventListeners() {
    window.addEventListener('load', function () {
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

                        let eventStatus = '';
                        const eventDate = new Date(event.eventdate);
                        const currentDate = new Date();
                        const eventImage = event.event_image ? '/event/' + event.event_image : '/event/default.jpg';
                        if (eventDate < currentDate) {
                            eventStatus = 'Finished';
                        } else if (eventDate > currentDate) {
                            eventStatus = 'Upcoming';
                        }
                        let description = event.description;
                            if (description.length > 70){
                                description = description.substring(0, 67) + '...';
                            }
                        let eventdate = event.eventdate;
                        let date = eventdate[8] + eventdate[9] + '/' + eventdate[5] + eventdate[6] + '/' + eventdate[0] + eventdate[1] + eventdate[2] + eventdate[3];
                        let time = eventdate[11] + eventdate[12] + 'h' + eventdate[14] + eventdate[15];
                        let today = new Date();
                        let nextWeek = new Date();
                        nextWeek.setDate(today.getDate() + 7);

                        if(eventDate < today) {
                            eventStatus = 'Finished';
                        } else if(eventDate > today) {
                            eventStatus = 'Upcoming';
                        }

                        eventCard.innerHTML = `
                            <a href="/event/${event.id}">
                                <p class="status" id="${eventStatus}">${eventStatus}</p>
                                <img src="${eventImage}" alt="Event Image" class="event-image">
                                
                                <div class="event-info">
                                    <h3>${event.name}</h3>
                                    <p>${description}</p>
                                    <p class=${eventStatus}> &#128197; ${date} &#128336; ${time}</p>
                                </div>
                            </a>
                        `;
                        eventsContainer.appendChild(eventCard);
                    });
                    if (events.length === 0) {
                        const noEventsMessage = document.createElement('p');
                        noEventsMessage.textContent = 'No events found.';
                        noEventsMessage.style.fontSize = '1.5em';
                        eventsContainer.appendChild(noEventsMessage);
                    }

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

    let eventsContainers = document.getElementById('eventsContainer');
    if(eventsContainers){
        let queryString = eventsContainers.dataset.query;
        const url = `/api/events-ajax?page=${page}&${queryString}`;
        xhr.open('GET', url, true);
        xhr.send();
    }
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
            option.addEventListener("click", () => {
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
                    optionsDiv.style = "position: absolute; bottom: 8px; left: 13%;"
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
            const eventId = fakebutton.id;
            const participant_card = fakebutton.parentElement;
            const participantId = participant_card.id;
            const sureboxExists = participant_card.querySelector(".surebox");

            if (!sureboxExists) {
                const surebox = document.createElement("div");
                surebox.classList.add("surebox");
                surebox.style.marginLeft = "20px";
                surebox.innerHTML = `
                    <p>Are you sure ?</p>
                    <div class="surebox-buttons">
                        <button type="button" class="surebox button yes">Yes</button>
                        <button type="button" class="surebox button no">No</button>
                    </div>
                `;
                fakebutton.parentElement.appendChild(surebox);
                const noButton = surebox.querySelector(".surebox.button.no");
                noButton.addEventListener("click", () => {
                    surebox.remove();
                });

                const yesButton = surebox.querySelector(".surebox.button.yes");
                yesButton.addEventListener("click", () => {
                    surebox.remove();
                    const participantsDiv = participant_card.parentElement;
                    participant_card.remove();
                    if (participantsDiv.childElementCount == 1 && participantsDiv.firstElementChild.id == 'owner') {
                        const noRequestsToJoin = document.createElement("h4");
                        noRequestsToJoin.textContent = "No participants yet";
                        participantsDiv.appendChild(noRequestsToJoin);
                    }
                    sendAjaxRequest('POST', `/event/${eventId}/participants/${participantId}/remove`, null, function () {});
                });
            }
        })
    });
    closeSureOptions()
}

function deleteAccount() {
    const deleteAccountButton = document.querySelector(".fake.button.delete-account");
    if (deleteAccountButton) {
        deleteAccountButton.addEventListener("click", () => {
            const accountId = deleteAccountButton.id;
            const sureboxExists = deleteAccountButton.parentElement.querySelector(".surebox");

            if (!sureboxExists) {
                const surebox = document.createElement("div");
                surebox.classList.add("surebox");
                surebox.style.position = "absolute";
                var position = deleteAccountButton.getBoundingClientRect();
                surebox.style.left = (position.left + parseInt(window.scrollX) + 200).toString() + "px";
                surebox.style.top = (position.top + parseInt(window.scrollY) - 20).toString() + "px";
                surebox.innerHTML = `
                    <p>Are you sure ?</p>
                    <div class="surebox-buttons">
                        <a class="surebox button yes">Yes</a>
                        <a class="surebox button no">No</a>
                    </div>
                `;
                deleteAccountButton.parentElement.appendChild(surebox);
                const noButton = surebox.querySelector(".surebox.button.no");
                noButton.addEventListener("click", () => {
                    surebox.remove();
                });

                const yesButton = surebox.querySelector(".surebox.button.yes");
                yesButton.addEventListener("click", () => {
                    sendAjaxRequest('DELETE', `/user/${accountId}/delete`, null, function () {
                        window.location.href = "/logout";
                    });
                });
            }
        });
    }
    closeSureOptions()
}

function deleteEvent() {
    const deleteEventButton = document.querySelector(".fake.button.delete-event");
    if (deleteEventButton) {
        deleteEventButton.addEventListener("click", () => {
            const eventId = deleteEventButton.id;
            const sureboxExists = deleteEventButton.parentElement.querySelector(".surebox");
            if (!sureboxExists) {
                const surebox = document.createElement("div");
                surebox.classList.add("surebox");
                surebox.style.position = "absolute";
                var positions = deleteEventButton.getBoundingClientRect();
                surebox.style.left = (positions.left + parseInt(window.scrollX) - 10).toString() + "px";
                surebox.style.top = (positions.top + parseInt(window.scrollY) + 50).toString() + "px";
                surebox.innerHTML = `
                    <p>Are you sure ?</p>
                    <div class="surebox-buttons">
                        <a class="surebox button yes">Yes</a>
                        <a class="surebox button no">No</a>
                    </div>
                `;
                deleteEventButton.parentElement.appendChild(surebox);
                const noButton = surebox.querySelector(".surebox.button.no");
                noButton.addEventListener("click", () => {
                    surebox.remove();
                });

                const yesButton = surebox.querySelector(".surebox.button.yes");
                yesButton.addEventListener("click", () => {
                    sendAjaxRequest('DELETE', `/event/${eventId}/delete`, null, function () {
                        window.location.href = "/home";
                    });
                });
            }
        });
    }
    closeSureOptions()
}

function postComment(){
    const commentButton = document.querySelector("button.add-comment");
    if(commentButton){
        commentButton.addEventListener("click", () => {
            const inputs = commentButton.parentElement.querySelectorAll("input");
            const commentTextArea = commentButton.parentElement.querySelector("textarea");
            const comment = commentTextArea.value;
            commentTextArea.value = "";

            if(comment.length > 500){
                const errorMessage = document.createElement('span');
                errorMessage.textContent = 'Comment can not be that size.';
                errorMessage.classList.add("error");
                commentButton.parentElement.insertBefore(errorMessage, commentButton);
                return;
            }

            sendAjaxRequest('POST', `/comment`, { comment: comment, id_user: inputs[0].value, id_event: inputs[1].value }, function (data) {
                const jsonData = JSON.parse(data.target.response)
                const comments = document.querySelector(".comments");

                let smallElement;
                let isUser = false;

                if(jsonData.id_user == jsonData.owner){
                    isUser = true;
                    smallElement = `
                    <div class="event-owner-message">
                        <h4>${jsonData.username}</h3>
                        <p class="event-owner">Event Owner Message</p>
                    </div>
                    `;
                }
                else {
                    smallElement = `
                    <h4>${jsonData.username}</h3>
                    `;
                }
                const commentElement = `
                <div id="${jsonData.id}" class="comment">
                    <div class="comment-header">
                        <div class="comment-header-title-likes">
                            ${smallElement}
                            <div class="likes-dislikes">
                                <p class="comment-like-number">0</p>
                                <img id="${jsonData.id_user}" class="comment-like" src="/icons/like.png" alt="like">
                                <p class="comment-dislike-number">0</p>
                                <img id="${jsonData.id_user}" class="comment-dislike" src="/icons/like.png" alt="dislike">
                            </div>
                        </div>
                        <div class="comment-actions">
                            <button class="fake button edit-comment no-button" id="${jsonData.id}">
                            &#9998;
                            </button>
                            <button class="fake button delete-comment no-button" id="${jsonData.id}">
                            &#128465;
                            </button>
                        </div>
                    </div>
                    <p class="comment-text">${jsonData.text}</p>
                    <p class="comment-date">${jsonData.date}</p>
                </div>
                `;

                if(comments.querySelector("h4")){
                    comments.querySelector("h4").remove();
                    const commentList = document.createElement("ul");
                    commentList.classList.add("comment-list");
                    const commentLi = document.createElement("li");
                    commentLi.innerHTML = commentElement;
                    commentList.appendChild(commentLi);
                    comments.appendChild(commentList);
                }
                else {
                    const commentLi = document.createElement("li");
                    commentLi.innerHTML = commentElement;
                    if(isUser){
                        comments.querySelector("ul").prepend(commentLi);
                    }
                    else {
                        comments.querySelector("ul").appendChild(commentLi);
                    }
                }
                likeComment();
                dislikeComment();
                deleteComment();
                editComment();
            });
        });
    }
}

function deleteComment() {
    const deleteCommentButtons = document.querySelectorAll(".fake.button.delete-comment");
    deleteCommentButtons.forEach((deleteCommentButton) => {
        deleteCommentButton.addEventListener("click", () => {
            const commentId = deleteCommentButton.id;
            const sureboxExists = deleteCommentButton.parentElement.querySelector(".surebox");
            if (!sureboxExists) {
                const surebox = document.createElement("div");
                surebox.classList.add("surebox");
                surebox.style.position = "absolute";
                var buttonPositions = deleteCommentButton.getBoundingClientRect();
                surebox.style.left = (buttonPositions.left + parseInt(window.scrollX) + 100).toString() + "px";
                surebox.style.top = (buttonPositions.top + parseInt(window.scrollY) ).toString() + "px";
                surebox.innerHTML = `
                    <p>Are you sure ?</p>
                    <div class="surebox-buttons">
                        <a class="surebox button yes">Yes</a>
                        <a class="surebox button no">No</a>
                    </div>
                `;
                deleteCommentButton.parentElement.appendChild(surebox);
                const noButton = surebox.querySelector(".surebox.button.no");
                noButton.addEventListener("click", () => {
                    surebox.remove();
                });

                const yesButton = surebox.querySelector(".surebox.button.yes");
                yesButton.addEventListener("click", () => {
                    const comments = document.querySelector(".comments");
                    surebox.remove();
                    const ul = comments.querySelector(".comment-list");
                    if(ul.childElementCount == 1){
                        ul.remove();
                        const noComments = document.createElement("h4");
                        noComments.textContent = "No comments yet";
                        comments.appendChild(noComments);
                    }
                    else {
                        deleteCommentButton.parentElement.parentElement.parentElement.parentElement.remove();
                    }
                    sendAjaxRequest('DELETE', `/comment/${commentId}/delete`, null, function () { });
                });
            }
        });
    });
    closeSureOptions()
}

function editComment() {
    const editCommentButtons = document.querySelectorAll(".fake.button.edit-comment");
    editCommentButtons.forEach((editCommentButton) => {
        editCommentButton.addEventListener("click", () => {
            const commentId = editCommentButton.id;
            editCommentButton.parentElement.style.display = "none";
            const mainCommentDiv = editCommentButton.parentElement.parentElement.parentElement;
            const commentText = mainCommentDiv.querySelector(".comment-text");
            commentText.style.display = "none";
            const commentTextValue = commentText.textContent;

            const editCommentDiv = document.createElement("div");
            editCommentDiv.classList.add("edit-comment-form");
            editCommentDiv.action = `/comment/${commentId}/update`;
            editCommentDiv.method = "PUT";
            editCommentDiv.innerHTML = `
                <textarea name="comment" class="edit-comment-textarea" required>${commentTextValue}</textarea>
                <button type="button" class="cancel-edit-comment-button">Cancel</button>
                <button type="button" class="save-edit-comment-button">Save</button>
            `;
            mainCommentDiv.insertBefore(editCommentDiv, mainCommentDiv.querySelector(".comment-date"));

            const cancelEditCommentButton = editCommentDiv.querySelector(".cancel-edit-comment-button");
            cancelEditCommentButton.addEventListener("click", () => {
                editCommentDiv.remove();
                editCommentButton.parentElement.style.display = "flex";
                commentText.style.display = "block";
            });

            const saveEditCommentButton = editCommentDiv.querySelector(".save-edit-comment-button");
            saveEditCommentButton.addEventListener("click", () => {
                editCommentDiv.addEventListener("click", () => {
                    const editCommentTextArea = editCommentDiv.querySelector(".edit-comment-textarea");
                    const comment = editCommentTextArea.value;
                    sendAjaxRequest('PUT', `/comment/${commentId}/update`, { comment: comment }, function () { });
                    editCommentDiv.remove();
                    editCommentButton.parentElement.style.display = "flex";
                    commentText.style.display = "block";
                    commentText.textContent = comment;
                });
            });
        });
    });
}

function closeDecisionBox() {
    document.addEventListener("click", (e) => {
        const decisionBoxes = document.querySelectorAll(".decision_box");
        decisionBoxes.forEach((decisionBox) => {
            if (e.target.parentElement == decisionBox.parentElement || e.target.classList.contains("decision_box")) {
                return;
            }
            decisionBox.remove();
        });
    });
}

function requestToJoin(requestToJoinButton){
    const eventId = requestToJoinButton.id;
    if(requestToJoinButton.classList.contains("sent")){
        const sureboxExists = document.querySelector(".surebox");

        if (!sureboxExists) {
            const surebox = document.createElement("div");
            surebox.classList.add("surebox");
            surebox.innerHTML = `
                <p>Cancel request to join ?</p>
                <div class="surebox-buttons">
                    <button type="button" class="surebox button yes">Yes</button>
                    <button type="button" class="surebox button no">No</button>
                </div>
            `;
            requestToJoinButton.parentElement.insertBefore(surebox, requestToJoinButton.nextSibling);
            const noButton = surebox.querySelector(".surebox.button.no");
            noButton.addEventListener("click", () => {
                surebox.remove();
            });

            const yesButton = surebox.querySelector(".surebox.button.yes");
            yesButton.addEventListener("click", () => {
                surebox.remove();
                requestToJoinButton.classList.remove("sent");
                requestToJoinButton.textContent = "Request To Join";
                sendAjaxRequest('POST', `/api/cancel-request-to-join`, {id_event: eventId}, function () {});

            });
        }
        closeSureOptions();
        return;
    }
    requestToJoinButton.textContent = "Request Sent";
    requestToJoinButton.classList.add("sent");
    sendAjaxRequest('POST', `/api/send-request-to-join`, {id_event: eventId}, function () {});
    closeSureOptions();
}

function requestToJoinDecision() {
    const requestsToJoin = document.querySelectorAll(".pending_request_to_join");
    requestsToJoin.forEach((requestToJoin) => {
        requestToJoin.addEventListener("click", () => {
            const requestToJoinId = requestToJoin.id;
            const decisionBox = requestToJoin.querySelector(".decision_box");
            if (!decisionBox) {
                const decisionBox = document.createElement("div");
                decisionBox.classList.add("decision_box");
                decisionBox.classList.add("notification");
                decisionBox.innerHTML = `
                    <button type="button" class="accept_request_to_join notification">&check;</button>
                    <button type="button" class="decline_request_to_join notification">&#10060;</button>
                `;
                requestToJoin.appendChild(decisionBox);

                const acceptRequestToJoin = decisionBox.querySelector(".accept_request_to_join");
                acceptRequestToJoin.addEventListener("click", () => {
                    const requestsToJoinDiv = requestToJoin.parentElement;
                    requestToJoin.remove();
                    if (!requestsToJoinDiv.childElementCount) {
                        const noRequestsToJoin = document.createElement("h4");
                        noRequestsToJoin.textContent = "No Requests To Join";
                        requestsToJoinDiv.appendChild(noRequestsToJoin);
                    }
                    sendAjaxRequest('POST', `/api/accept-request-to-join`, { id_requestToJoin: requestToJoinId }, function () { });
                });

                const declineRequestToJoin = decisionBox.querySelector(".decline_request_to_join");
                declineRequestToJoin.addEventListener("click", () => {
                    const requestsToJoinDiv = requestToJoin.parentElement;
                    requestToJoin.remove();
                    if (!requestsToJoinDiv.childElementCount) {
                        const noRequestsToJoin = document.createElement("h4");
                        noRequestsToJoin.textContent = "No Requests To Join";
                        requestsToJoinDiv.appendChild(noRequestsToJoin);
                    }
                    sendAjaxRequest('POST', `/api/deny-request-to-join`, { id_requestToJoin: requestToJoinId }, function () { });
                });
            }

        });
    });
    closeDecisionBox();
}

function editEvent() {
    const updateEventButton = document.querySelector(".btn.btn-primary");
    if(updateEventButton){
        updateEventButton.parentElement.addEventListener("submit", async (e) => {
            e.preventDefault();

            const formData = new FormData(e.target);

            fetch(e.target.action, {
                method: 'POST',
                body: new URLSearchParams(formData),
                keepalive: true
            }).then((response) => {
                if (!response.ok) {
                    throw new Error(`HTTP error! Status: ${response.status}`);
                }
                setTimeout(() => {
                    window.location.href = `/event/${updateEventButton.id}`;
                }, 1000);
                return response.json();
            }).then((jsonData) => {
                const jsonString = JSON.stringify(jsonData);
                sendAjaxRequest('POST', '/api/send-event-update', { id_event: updateEventButton.id, whatChanged: jsonString }, function () {});
            }).catch((error) => {
                console.error('Error:', error);
            });
        });
    }
}

function eventUpdate() {
    const eventUpdates = document.querySelectorAll(".pending_event_update");
    eventUpdates.forEach((eventUpdate) => {
        eventUpdate.addEventListener("mouseover", () => {
            const eventUpdateId = eventUpdate.id;
            const closeEventUpdateButton = eventUpdate.querySelector(".close_event_update");
            if (!closeEventUpdateButton) {
                const closeEventUpdateButton = document.createElement("p");
                closeEventUpdateButton.classList.add("close_event_update");
                closeEventUpdateButton.classList.add("notification");
                closeEventUpdateButton.textContent = "X";
                eventUpdate.appendChild(closeEventUpdateButton);
                closeEventUpdateButton.addEventListener("click", () => {
                    const eventUpdatesDiv = eventUpdate.parentElement;
                    eventUpdate.remove();
                    if (!eventUpdatesDiv.childElementCount) {
                        const noRequestsToJoin = document.createElement("h4");
                        noRequestsToJoin.textContent = "No Event Updates";
                        eventUpdatesDiv.appendChild(noRequestsToJoin);
                    }
                    sendAjaxRequest('POST', `/api/clear-event-update`, { id_eventUpdate: eventUpdateId }, function () { });
                });
            }
        });

        eventUpdate.addEventListener("mouseleave", () => {
            const closeEventUpdateButton = eventUpdate.querySelector(".close_event_update");
            if (closeEventUpdateButton) {
                closeEventUpdateButton.remove();
            }
        });
    });
}

function likeComment() {
    const likes = document.querySelectorAll(".comment-like");
    if(likes){
    likes.forEach((like) => {
        like.addEventListener("click", () => {
            const commentId = like.parentElement.parentElement.parentElement.parentElement.id;
            const userId = like.id;
            const dislike = like.parentElement.querySelector(".comment-dislike");
            const likesNumber = like.parentElement.querySelector(".comment-like-number");
            const dislikesNumber = dislike.parentElement.querySelector(".comment-dislike-number");
            if (like.classList.contains("comment-like-active")) {
                like.src = "/icons/like.png";
                dislike.src = "/icons/like.png";
                likesNumber ? likesNumber.textContent = parseInt(likesNumber.textContent) - 1 : null;
                like.classList.remove("comment-like-active");
                dislike.classList.remove("comment-dislike-active");
                sendAjaxRequest('POST', '/api/comment/like', { action: 'remove', id_comment: commentId, id_user: userId }, function () { });
            }
            else {
                like.src = "/icons/blue_like.png";
                dislike.src = "/icons/like.png";
                likesNumber ? likesNumber.textContent = parseInt(likesNumber.textContent) + 1 : null;
                if (dislike.classList.contains("comment-dislike-active")) {
                    dislikesNumber ? dislikesNumber.textContent = parseInt(dislikesNumber.textContent) - 1 : null;
                }
                like.classList.add("comment-like-active");
                dislike.classList.remove("comment-dislike-active");
                sendAjaxRequest('POST', '/api/comment/like', { action: 'add', id_comment: commentId, id_user: userId }, function () { });
            }
        });
    });
    }
}

function dislikeComment() {
    const dislikes = document.querySelectorAll(".comment-dislike");
    dislikes.forEach((dislike) => {
        dislike.addEventListener("click", () => {
            const commentId = dislike.parentElement.parentElement.parentElement.parentElement.id;
            const userId = dislike.id;
            const like = dislike.parentElement.querySelector(".comment-like");
            const dislikesNumber = dislike.parentElement.querySelector(".comment-dislike-number");
            const likesNumber = like.parentElement.querySelector(".comment-like-number");
            if (dislike.classList.contains("comment-dislike-active")) {
                dislike.src = "/icons/like.png";
                like.src = "/icons/like.png";
                dislikesNumber ? dislikesNumber.textContent = parseInt(dislikesNumber.textContent) - 1 : null;
                dislike.classList.remove("comment-dislike-active");
                like.classList.remove("comment-like-active");
                sendAjaxRequest('POST', `/api/comment/dislike`, { action: 'remove', id_comment: commentId, id_user: userId }, function () { });
            }
            else {
                dislike.src = "/icons/blue_like.png";
                like.src = "/icons/like.png";
                dislikesNumber ? dislikesNumber.textContent = parseInt(dislikesNumber.textContent) + 1 : null;
                if (like.classList.contains("comment-like-active")) {
                    likesNumber ? likesNumber.textContent = parseInt(likesNumber.textContent) - 1 : null;
                }
                dislike.classList.add("comment-dislike-active");
                like.classList.remove("comment-like-active");
                sendAjaxRequest('POST', `/api/comment/dislike`, { action: 'add', id_comment: commentId, id_user: userId }, function () { });
            }
        });
    });
}
function createPoll() {
    const createPollFake = document.querySelector(".fake-poll-create-button");
    const eventIdHolder = document.querySelector(".event_id_holder");
    if (createPollFake) {
        const eventId = eventIdHolder.id;
        let optionNumber = 2;
        let provisionalId = 1;

        if (createPollFake) {
            
            createPollFake.addEventListener("click", () => {
                let pollNumber = document.querySelectorAll(".poll").length;
                const errorMessages = document.querySelectorAll('div[style="color: red;"]');
                errorMessages.forEach((errorMessage) => {
                    errorMessage.remove();
                });
                if(pollNumber >= 4){
                    const errorMessage = document.createElement('div');
                    errorMessage.textContent = 'Max polls reached';
                    errorMessage.style.color = 'red';
                    createPollFake.parentNode.insertBefore(errorMessage, createPollFake.nextSibling); 
                    return;
                }
                const noPolls = document.querySelector(".no-polls");
                noPolls ? noPolls.style.display = "none" : null;
                createPollFake.style.display = "none";
                const createPollForm = document.createElement("div");
                createPollForm.classList.add("create-poll-form");
                createPollForm.action = `/poll/store`;
                createPollForm.method = "POST";
                createPollForm.innerHTML = `
                    <input type="text" name="title" placeholder="Title" required>
                    <input type="text" name="option1" placeholder="Option 1" required>
                    <input type="text" name="option2" placeholder="Option 2" required>
            `;
                createPollFake.parentElement.appendChild(createPollForm);
                createPollFake.parentElement.insertBefore(createPollForm, createPollFake);
                const createPollOptions = document.createElement("div");
                createPollOptions.classList.add("add-poll-options");
                createPollOptions.innerHTML = `
                    <button type="button" class="add-option-button">Add Option</button>
            `;
                createPollForm.appendChild(createPollOptions);
                const addOptionButton = createPollOptions.querySelector(".add-option-button");
                addOptionButton.addEventListener("click", () => {
                    if (optionNumber < 10) {
                        optionNumber++;
                        const fullOption = document.createElement("div");
                        fullOption.classList.add("full-option");
                        fullOption.style.display = "flex";
                        fullOption.style.justifyContent = "space-between";
                        fullOption.style.flexDirection = "row";
                        const option = document.createElement("input");
                        option.type = "text";
                        option.name = `option${optionNumber}`;
                        option.placeholder = `Option ${optionNumber}`;
                        option.required = true;
                        const removeOptionButton = document.createElement("button");
                        removeOptionButton.type = "button";
                        removeOptionButton.classList.add("remove-option-button");
                        removeOptionButton.textContent = "Remove Option";
                        removeOptionButton.addEventListener("click", () => {
                            fullOption.remove();
                            optionNumber--;
                        });
                        fullOption.appendChild(option);
                        fullOption.appendChild(removeOptionButton);
                        createPollForm.appendChild(fullOption);
                        createPollForm.insertBefore(fullOption, createPollOptions);
                    }
                    if (optionNumber == 10) {
                        addOptionButton.disabled = true;
                        addOptionButton.style.display = "none";
                    }
                });
                const createPollButtons = document.createElement("div");
                createPollButtons.classList.add("create-poll-buttons");
                createPollButtons.innerHTML = `
                    <button type="button" class="cancel-create-poll-button">Cancel</button>
                    <button type="submit" class="create-poll-button">Create</button>
            `;
                createPollForm.appendChild(createPollButtons);
                const cancelCreatePollButton = createPollButtons.querySelector(".cancel-create-poll-button");
                cancelCreatePollButton.addEventListener("click", () => {
                    noPolls ? noPolls.style.display = "block" : null;
                    createPollForm.remove();
                    createPollFake.style.display = "block";
                });
                const createPollButton = createPollButtons.querySelector(".create-poll-button");
                createPollButton.addEventListener("click", () => {
                    const errorMessages = document.querySelectorAll('div[style="color: red;"]');
                    errorMessages.forEach((errorMessage) => {
                        errorMessage.remove();
                    }
                    );
                    const title = createPollForm.querySelector("input[name='title']").value;
                    if (!title) {
                        const errorMessage = document.createElement('div');
                        errorMessage.textContent = 'Title can not be empty.';
                        errorMessage.style.color = 'red';
                        createPollForm.insertBefore(errorMessage, createPollButtons);
                        return;
                    }
                    if (title.length > 150 || title.length < 2) {
                        const errorMessage = document.createElement('div');
                        errorMessage.textContent = 'Title can not be that size.';
                        errorMessage.style.color = 'red';
                        createPollForm.insertBefore(errorMessage, createPollButtons);
                        return;
                    }
                    const options = [];
                    for (let i = 1; i <= optionNumber; i++) {
                        options.push(createPollForm.querySelector(`input[name='option${i}']`).value);
                        if (!options[i - 1] || options[i - 1].length > 50 || options[i - 1].length < 2) {
                            const errorMessage = document.createElement('div');
                            errorMessage.textContent = 'Option names can not be that size.';
                            errorMessage.style.color = 'red';
                            createPollForm.insertBefore(errorMessage, createPollButtons);
                            return;
                        }
                    }

                    if (options.length !== new Set(options).size) {
                        const errorMessage = document.createElement('div');
                        errorMessage.textContent = 'Option names must be distinct.';
                        errorMessage.style.color = 'red';
                        createPollForm.insertBefore(errorMessage, createPollButtons);
                        return;
                    }

                    createPollForm.remove();
                    createPollFake.style.display = "block";
                    const poll = document.createElement("li");
                    poll.style.listStyleType = "none";
                    poll.classList.add("poll");
                    poll.innerHTML = `
                        <div class="poll-header">
                        <h3>${title}</h3>
                        <button type="button" class="fake-poll-delete-button no-button">&#128465;</button>
                        </div>
                        <ul class="poll-options">
                        </ul>
                    `;
                    poll.querySelector(".poll-header").style.display = "flex";
                    poll.querySelector(".fake-poll-delete-button").addEventListener("click", () => {
                        poll.remove();
                        createPollFake.style.display = "block";
                        const checkPolls = document.querySelectorAll(".poll");
                        if (!checkPolls.length) {
                            const noPolls = document.createElement("p");
                            noPolls.classList.add("no-polls");
                            noPolls.textContent = "No Polls";
                            createPollFake.parentElement.appendChild(noPolls);
                        }
                        sendAjaxRequest('DELETE', `/api/poll/delete`, { eventId: eventId, title: title }, function () { });
                        noPolls ? noPolls.style.display = "block" : null;
                        pollNumber--;
                    });

                    createPollFake.parentElement.appendChild(poll);
                    const pollOptions = poll.querySelector(".poll-options");
                    options.forEach((option) => {
                        const pollOption = document.createElement("li");
                        pollOption.classList.add("poll-option");
                        const pollOptionLabel = document.createElement("label");
                        pollOptionLabel.style.display = "flex";
                        pollOptionLabel.style.flexDirection = "row";
                        pollOptionLabel.innerHTML = `
                            <input type="radio" name="poll-option ${provisionalId}" value="${option}">
                            <p>${option} - 0</p>
                        `;
                        pollOption.appendChild(pollOptionLabel);
                        pollOptions.appendChild(pollOption);
                    }
                    );
                    if (noPolls) {
                        noPolls.remove();
                    }
                    sendAjaxRequest('POST', `/api/poll/store`, { title: title, options: JSON.stringify(options), eventId: eventId }, function () { });
                    optionNumber = 2;
                    provisionalId++;
                    pollNumber++;
                    const pollOptionsInputs = poll.querySelectorAll(".poll-option input[type='radio']");
                    const pollOptionsChecked = poll.querySelectorAll(".poll-option input[type='radio']:checked");
                    let checkedBefore = pollOptionsChecked[0] ? pollOptionsChecked[0] : null;
                    pollOptionsInputs.forEach((pollOptionInput) => {
                        pollOptionInput.addEventListener("click", (event) => {
                            event.stopPropagation();
                            const eventId = document.querySelector(".event_id_holder").id;
                            const title = poll.querySelector("h3").textContent;
                            const option = pollOptionInput.parentElement.querySelector("p").textContent.substring(0, pollOptionInput.parentElement.querySelector("p").textContent.indexOf(" - "));
                            let votes = parseInt(pollOptionInput.parentElement.querySelector("p").textContent.split(" - ")[1]);
                            const beforeOption = checkedBefore ? checkedBefore.parentElement.querySelector("p").textContent.substring(0, checkedBefore.parentElement.querySelector("p").textContent.indexOf(" - ")) : null;
                            let beforeVotes = checkedBefore ? parseInt(checkedBefore.parentElement.querySelector("p").textContent.split(" - ")[1]) : null;
                            if (checkedBefore && checkedBefore.value == pollOptionInput.value) {
                                votes -= 1;
                                pollOptionInput.checked = false;
                                checkedBefore = null;
                                pollOptionInput.parentElement.querySelector("p").textContent = `${option} - ${votes}`;
                                sendAjaxRequest('DELETE', `/api/poll/unvote`, { eventId: eventId, title: title, option: option, votes: votes }, function () { });
                            } else if (checkedBefore && checkedBefore.value != pollOptionInput.value) {
                                votes += 1;
                                beforeVotes -= 1;
                                checkedBefore.parentElement.querySelector("p").textContent = `${beforeOption} - ${beforeVotes}`;
                                checkedBefore = pollOptionInput;
                                sendAjaxRequest('DELETE', `/api/poll/unvote`, { eventId: eventId, title: title, option: beforeOption, votes: beforeVotes }, function () { });
                                pollOptionInput.parentElement.querySelector("p").textContent = `${option} - ${votes}`;
                                sendAjaxRequest('PUT', `/api/poll/vote`, { eventId: eventId, title: title, option: option, votes: votes }, function () { });
                                pollOptionInput.classList.add("user_vote");
                                pollOptionInput.checked = true;
                            } else {
                                votes += 1;
                                checkedBefore = pollOptionInput;
                                pollOptionInput.parentElement.querySelector("p").textContent = `${option} - ${votes}`;
                                sendAjaxRequest('PUT', `/api/poll/vote`, { eventId: eventId, title: title, option: option, votes: votes }, function () { });
                                pollOptionInput.classList.add("user_vote");
                                pollOptionInput.checked = true;
                            }
                        });
                    }
                    );
                });
            });
        }
    }

}


function deletePoll() {
    const deletePollButtons = document.querySelectorAll(".fake-poll-delete-button");
    deletePollButtons.forEach((deletePollButton) => {
        deletePollButton.addEventListener("click", () => {
            const poll = deletePollButton.parentElement.parentElement;
            const eventId = document.querySelector(".event_id_holder").id;
            const title = deletePollButton.parentElement.querySelector("h4").textContent;
            poll.remove();
            sendAjaxRequest('DELETE', `/api/poll/delete`, { eventId: eventId, title: title }, function () { });
        });
    });
}

function answerPoll() {
    const polls = document.querySelectorAll(".poll");
    polls.forEach((poll) => {
        const pollOptions = poll.querySelectorAll(".poll-option input[type='radio']");
        pollOptions.forEach((pollOptionInput) => {
            if (!pollOptionInput.classList.length) {
                pollOptionInput.checked = false;
            } else {
                pollOptionInput.checked = true;
            }
        });
        const pollOptionsChecked = poll.querySelectorAll(".poll-option input[type='radio']:checked");
        let checkedBefore = pollOptionsChecked[0] ? pollOptionsChecked[0] : null;
        pollOptions.forEach((pollOptionInput) => {
            pollOptionInput.addEventListener("click", (event) => {
                event.stopPropagation();
                const eventId = document.querySelector(".event_id_holder").id;
                const title = poll.querySelector("h4").textContent;
                const option = pollOptionInput.parentElement.querySelector("p").textContent.substring(0, pollOptionInput.parentElement.querySelector("p").textContent.indexOf(" - "));
                let votes = parseInt(pollOptionInput.parentElement.querySelector("p").textContent.split(" - ")[1]);
                const beforeOption = checkedBefore ? checkedBefore.parentElement.querySelector("p").textContent.substring(0, checkedBefore.parentElement.querySelector("p").textContent.indexOf(" - ")) : null;
                let beforeVotes = checkedBefore ? parseInt(checkedBefore.parentElement.querySelector("p").textContent.split(" - ")[1]) : null;
                if (checkedBefore && checkedBefore.value == pollOptionInput.value) {
                    votes -= 1;
                    pollOptionInput.checked = false;
                    checkedBefore = null;
                    pollOptionInput.parentElement.querySelector("p").textContent = `${option} - ${votes}`;
                    sendAjaxRequest('DELETE', `/api/poll/unvote`, { eventId: eventId, title: title, option: option, votes: votes }, function () { });
                } else if (checkedBefore && checkedBefore.value != pollOptionInput.value) {
                    votes += 1;
                    beforeVotes -= 1;
                    checkedBefore.parentElement.querySelector("p").textContent = `${beforeOption} - ${beforeVotes}`;
                    checkedBefore = pollOptionInput;
                    sendAjaxRequest('DELETE', `/api/poll/unvote`, { eventId: eventId, title: title, option: beforeOption, votes: beforeVotes }, function () { });
                    pollOptionInput.parentElement.querySelector("p").textContent = `${option} - ${votes}`;
                    sendAjaxRequest('PUT', `/api/poll/vote`, { eventId: eventId, title: title, option: option, votes: votes }, function () { });
                    pollOptionInput.classList.add("user_vote");
                    pollOptionInput.checked = true;
                } else {
                    votes += 1;
                    checkedBefore = pollOptionInput;
                    pollOptionInput.parentElement.querySelector("p").textContent = `${option} - ${votes}`;
                    sendAjaxRequest('PUT', `/api/poll/vote`, { eventId: eventId, title: title, option: option, votes: votes }, function () { });
                    pollOptionInput.classList.add("user_vote");
                    pollOptionInput.checked = true;
                }

            });


        });
    });
}




function closeNotifications() {
    document.addEventListener("click", (e) => {
        if (e.target.classList.contains("user-notifications-container")
            || e.target.classList.contains("user-notifications")
            || e.target.classList.contains("notifications-icon")
            || e.target.classList.contains("notification")) {
            return;
        }
        const notifications = document.querySelector(".user-notifications-container");
        if (notifications) {
            notifications.style.display = "none";
        }
    });
}

function moveNotifications() {
    const notificationsIconDiv = document.querySelector(".notifications-icon");
    if(notificationsIconDiv){
        const notifications = document.querySelector(".user-notifications-container");
        if (notifications && notificationsIconDiv && notifications.style.display == "block") {
            const position = notificationsIconDiv.getBoundingClientRect();
            notifications.style.left = (position.left - 150).toString() + "px";
            notifications.style.top = (position.top + 80).toString() + "px";
        }
    }
}

function openNotificaitons() {
    const notificationsIconDiv = document.querySelector(".notifications-icon");
    const notifications = document.querySelector(".user-notifications-container");
    if (notificationsIconDiv && notifications) {
        notificationsIconDiv.addEventListener("click", () => {
            const position = notificationsIconDiv.getBoundingClientRect();
            if (notifications.style.display == "none") {
                notifications.style.display = "block";
                notifications.style.position = "absolute";
                notifications.style.left = (position.left - 150).toString() + "px";
                notifications.style.top = (position.top + 80).toString() + "px";
            }
            else {
                notifications.style.display = "none";
            }
        });
    }
    closeNotifications();
}

function banAccount() {
    const banAccountButton = document.querySelector(".fake.button.ban-user");
    const username = document.querySelector(".profile-header h1");
    if (banAccountButton) {
        banAccountButton.addEventListener("click", () => {
            const accountId = banAccountButton.id;
            const sureboxExists = banAccountButton.parentElement.querySelector(".surebox");

            if (!sureboxExists) {
                const surebox = document.createElement("div");
                surebox.classList.add("surebox");
                surebox.innerHTML = `
                    <p>Are you sure ?</p>
                    <div class="surebox-buttons">
                        <a class="surebox button yes">Yes</a>
                        <a class="surebox button no">No</a>
                    </div>
                `;
                const buttonsDiv = banAccountButton.parentElement;
                buttonsDiv.style.display = "flex";
                buttonsDiv.style.flexDirection = "row";
                buttonsDiv.appendChild(surebox);
                surebox.style.marginLeft = "20px";
                const noButton = surebox.querySelector(".surebox.button.no");
                noButton.addEventListener("click", () => {
                    surebox.remove();
                });

                const yesButton = surebox.querySelector(".surebox.button.yes");
                yesButton.addEventListener("click", () => {
                    surebox.remove();
                    if(banAccountButton.textContent == "Ban User"){
                        banAccountButton.textContent = "Unban User";
                        username.textContent.concat(" [BANNED]");
                    } else {
                        banAccountButton.textContent = "Ban User";
                    }
                    sendAjaxRequest('PUT', `/user/${accountId}/ban`, null, function () {
                        window.location.href = `/user/${accountId}`;
                    });
                });
            }
        });
    }
    closeSureOptions()
}

function createLocation()
{
    const createLocationFake = document.querySelector(".fake-add-location");
    let openCreate = 0;
    if(createLocationFake)
    {
        createLocationFake.addEventListener("click", () => {
            if(openCreate == 1){
                return;
            }
            openCreate++;
            const createLocationForm = document.createElement("div");
            createLocationForm.classList.add("create-location-form");
            createLocationForm.action = `api/location/store`;
            createLocationForm.method = "POST";
            createLocationForm.innerHTML = `
                <input type="text" name="name" placeholder="Name" required>
                <input type="text" name="address" placeholder="Address" required>
                <input type="text" name="city" placeholder="City" required>
                <input type="text" name="country" placeholder="Country" required>
                <button type="button" class="cancel-create-location-button">Cancel</button>
                <button type="button" class="create-location-button">Create</button>
            `;
            createLocationFake.parentElement.appendChild(createLocationForm);
            createLocationFake.parentElement.parentNode.insertBefore(createLocationForm, createLocationFake.parentElement.nextSibling);
            const cancelCreateLocationButton = createLocationForm.querySelector(".cancel-create-location-button");
            cancelCreateLocationButton.addEventListener("click", () => {
                openCreate--;
                createLocationForm.remove();
            });
            const createLocationButton = createLocationForm.querySelector(".create-location-button");
            createLocationButton.addEventListener("click", () => {
                const errorMessages = document.querySelectorAll('div[style="color: red;"]');
                errorMessages.forEach((errorMessage) => {
                    errorMessage.remove();
                });
                const name = createLocationForm.querySelector("input[name='name']").value;
                const address = createLocationForm.querySelector("input[name='address']").value;
                const city = createLocationForm.querySelector("input[name='city']").value;
                const country = createLocationForm.querySelector("input[name='country']").value;
                if (!name || !address || !city || !country) {
                    const errorMessage = document.createElement('div');
                    errorMessage.textContent = 'All fields are required.';
                    errorMessage.style.color = 'red';
                    createLocationForm.insertBefore(errorMessage, createLocationForm.querySelector(".create-location-buttons"));
                    return;
                }
                if (name.length > 50 || name.length < 1 || address.length > 50 || address.length < 10 || city.length > 50 || city.length < 2|| country.length > 50 || country.length < 2) {
                    const errorMessage = document.createElement('div');
                    errorMessage.textContent = 'Fields can not be that size.';
                    errorMessage.style.color = 'red';
                    createLocationForm.insertBefore(errorMessage, createLocationForm.querySelector(".create-location-buttons"));
                    return;
                }
                const fullAddress = `${address}, ${city}, ${country}`;
                let locationId;
                sendAjaxRequest('POST', `/api/location/store`, { name: name, address: fullAddress }, function (data) {
                    locationId = JSON.parse(data.target.response).id;
                    createLocationForm.remove();
                    const LocationSelect = document.querySelector(".location-select");
                    const option = document.createElement("option");
                    option.value = locationId;
                    option.textContent = name;
                    option.selected = true;
                    LocationSelect.appendChild(option);
                }); 
                openCreate--;
            });
        });

    }
}

function deleteLocation(){
    const fullLocation = document.querySelector(".full-event-location");
    if(fullLocation){
        const isAdmin = fullLocation.dataset.isAdmin;
        const eventId = fullLocation.dataset.eventId;
        let locationId = document.querySelector(".full-event-location").id;
        if(isAdmin == 'false'){
            return;
        }
        if(locationId == 79){
            return;
        }
        const deleteLocationButton = document.createElement("button");
        deleteLocationButton.classList.add("delete-location-button");
        deleteLocationButton.textContent = "Delete Location";
        fullLocation.appendChild(deleteLocationButton);
        fullLocation.style.display = "flex";
        fullLocation.style.flexDirection = "row";
        fullLocation.style.justifyContent = "space-between";
        deleteLocationButton.addEventListener("click", () => {
            locationId = document.querySelector(".full-event-location").id;
            if(locationId == 79){
                return;
            }
            deleteLocationButton.remove();
            fullLocation.remove();
            const newfullLocation = document.createElement("div");
            newfullLocation.id = "79";
            newfullLocation.classList.add("full-event-location");
            newfullLocation.dataset.isAdmin = isAdmin;
            newfullLocation.dataset.eventId = eventId;

            const locationInfo = document.createElement("div");
            locationInfo.classList.add("location-info");

            const locationName = document.createElement("p");
            locationName.textContent = "Location: To be determined";
            locationInfo.appendChild(locationName);

            const locationAddress = document.createElement("p");
            locationAddress.textContent = "Address: To be determined";
            locationInfo.appendChild(locationAddress);
            newfullLocation.appendChild(locationInfo);
            const eventInfo = document.querySelector(".event-info");
            eventInfo.appendChild(newfullLocation);
            sendAjaxRequest('DELETE', `/api/location/delete`, { id_location: locationId, id_event: eventId }, function () { });
        });
    }
}

function moveSureboxDeleteAccount() {
    const deleteAccountButton = document.querySelector(".fake.button.delete-account");
    if(deleteAccountButton){
        const surebox = document.querySelector(".surebox");
        if(surebox){
            var position = deleteAccountButton.getBoundingClientRect();
            surebox.style.left = (position.left + parseInt(window.scrollX) + 200).toString() + "px";
            surebox.style.top = (position.top + parseInt(window.scrollY) - 20).toString() + "px";
        }
    }
}

function moveSureboxDeleteEvent() {
    const deleteEventButton = document.querySelector(".fake.button.delete-event");
    if(deleteEventButton){
        const surebox = document.querySelector(".surebox");
        if(surebox){
            var position = deleteEventButton.getBoundingClientRect();
            surebox.style.left = (position.left + parseInt(window.scrollX) - 10).toString() + "px";
            surebox.style.top = (position.top + parseInt(window.scrollY) + 50).toString() + "px";
        }
    }
}

function moveSureBoxDeleteComment() {
    const deleteCommentButton = document.querySelector(".fake.button.delete-comment");
    if(deleteCommentButton){
        const surebox = document.querySelector(".surebox");
        if(surebox){
            var position = deleteCommentButton.getBoundingClientRect();
            surebox.style.left = (position.left + parseInt(window.scrollX) + 100).toString() + "px";
            surebox.style.top = (position.top + parseInt(window.scrollY) ).toString() + "px";
        }
    }
}

function moveSureBoxRequestAdmin() {
    const requestAdminButton = document.querySelector(".request-admin");
    if(requestAdminButton){
        const surebox = document.querySelector(".surebox");
        if(surebox){
            var position = requestAdminButton.getBoundingClientRect();
            surebox.style.left = (position.left + parseInt(window.scrollX) + 320).toString() + "px";
            surebox.style.top = (position.top + parseInt(window.scrollY) - 10).toString() + "px";
        }
    }
}

function respondAdminRequest() {
    const fakebuttons = document.querySelectorAll(".fake.button.accept");
    fakebuttons.forEach((fakebutton) => {
        fakebutton.addEventListener("click", () => {
            const userId = fakebutton.id;
            const user_card = fakebutton.parentElement;
            const sureboxExists = user_card.querySelector(".surebox");

            if (!sureboxExists) {
                const surebox = document.createElement("div");
                surebox.classList.add("surebox");
                surebox.style.marginLeft = "20px";
                surebox.innerHTML = `
                    <p>Make this user Admin ?</p>
                    <div class="surebox-buttons">
                        <button type="button" class="surebox button yes">Yes</button>
                        <button type="button" class="surebox button no">No</button>
                    </div>
                `;
                fakebutton.parentElement.appendChild(surebox);
                const noCandidates = document.createElement("h4");
                noCandidates.textContent = "No candidates";
                const noButton = surebox.querySelector(".surebox.button.no");
                noButton.addEventListener("click", () => {
                    surebox.remove();
                    if (usersDiv.childElementCount == 0) {
                        usersDiv.appendChild(noCandidates);
                    }
                    sendAjaxRequest('PUT', `/adminCandidates/${userId}/refuse`, null, function () { });
                });

                const yesButton = surebox.querySelector(".surebox.button.yes");
                yesButton.addEventListener("click", () => {
                    surebox.remove();
                    const usersDiv = user_card.parentElement;
                    user_card.remove();
                    if (usersDiv.childElementCount == 0) {
                        usersDiv.appendChild(noCandidates);
                    }
                    console.log("Accepted");
                    sendAjaxRequest('PUT', `/adminCandidates/${userId}/accept`, null, function () { });
                });
            }
        })
    });
    closeSureOptions()
}

function requestAdmin() {
    const requestAdminButton = document.querySelector(".request-admin");
    if(requestAdminButton){
        requestAdminButton.addEventListener("click", () => {
            const userId = requestAdminButton.id;
            if(requestAdminButton.classList.contains("sent")){
                const sureboxExists = document.querySelector(".surebox");

                if (!sureboxExists) {
                    const surebox = document.createElement("div");
                    surebox.classList.add("surebox");
                    surebox.style.position = "absolute";
                    var position = requestAdminButton.getBoundingClientRect();
                    surebox.style.left = (position.left + parseInt(window.scrollX) + 320).toString() + "px";
                    surebox.style.top = (position.top + parseInt(window.scrollY) - 10).toString() + "px";
                    surebox.innerHTML = `
                        <p>Cancel request admin ?</p>
                        <div class="surebox-buttons">
                            <button type="button" class="surebox button yes">Yes</button>
                            <button type="button" class="surebox button no">No</button>
                        </div>
                    `;
                    requestAdminButton.parentElement.appendChild(surebox);
                    const noButton = surebox.querySelector(".surebox.button.no");
                    noButton.addEventListener("click", () => {
                        surebox.remove();
                    });
        
                    const yesButton = surebox.querySelector(".surebox.button.yes");
                    yesButton.addEventListener("click", () => {
                        surebox.remove();
                        requestAdminButton.classList.remove("sent");
                        requestAdminButton.textContent = "Request Admin Permissions";
                        sendAjaxRequest('PUT', `/user/${userId}/cancel-request-admin`, null, function () {});
                    });
                }
                closeSureOptions();
                return;
            }
            requestAdminButton.classList.add("sent");
            requestAdminButton.textContent = "Request Admin Permissions Sent";
            sendAjaxRequest('PUT', `/user/${userId}/request-admin`, null, function () { });
        });
    }
    closeSureOptions();
}

addEventListeners();
openOptions();
closeOptions();
switchEvents();
removeParticipant();
deleteAccount();
deleteEvent();
deleteComment();
editComment();
requestToJoinDecision();
eventUpdate();
likeComment();
dislikeComment();
createPoll();
deletePoll();
answerPoll();
openNotificaitons();
banAccount();
createLocation();
deleteLocation();
editEvent();
respondAdminRequest();
requestAdmin();
postComment()

function moves(){
    moveNotifications();
    moveSureboxDeleteAccount();
    moveSureboxDeleteEvent();
    moveSureBoxDeleteComment();
    moveSureBoxRequestAdmin();
}

window.onresize = moves;
window.onscroll = moves;
