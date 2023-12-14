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

                        let eventStatus = '';
                        const eventDate = new Date(event.eventdate);
                        const currentDate = new Date();

                        if (eventDate < currentDate) {
                            eventStatus = 'Finished';
                        } else if (eventDate.toDateString() === currentDate.toDateString()) {
                            eventStatus = 'Today';
                        } else if (eventDate > currentDate) {
                            eventStatus = 'Upcoming';
                        }

                        eventCard.innerHTML = `
                            <a href="/event/${event.id}">
                                <img src="/images/event_default.png" alt="Event Image" class="event-image">
                                
                                <div class="event-info">
                                    <h3>${event.name}</h3>
                                    <p>${event.description}</p>
                                    <p>${event.eventdate}</p>
                                    <p>${eventStatus}</p>
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
    closeSureOptions()
}

function deleteAccount() {
    const deleteAccountButton = document.querySelector(".fake.button.delete-account");
    if (deleteAccountButton) {
        deleteAccountButton.addEventListener("click", (e) => {
            const accountId = deleteAccountButton.id;
            const sureboxExists = deleteAccountButton.parentElement.querySelector(".surebox");

            if (!sureboxExists) {
                const surebox = document.createElement("div");
                surebox.classList.add("surebox");
                surebox.style.position = "absolute";
                surebox.style.left = (parseInt(e.clientX) + parseInt(window.scrollX) - 100).toString() + "px";
                surebox.style.top = (parseInt(e.clientY) + parseInt(window.scrollY) - 250).toString() + "px";
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
        deleteEventButton.addEventListener("click", (e) => {
            const eventId = deleteEventButton.id;
            const sureboxExists = deleteEventButton.parentElement.querySelector(".surebox");
            if (!sureboxExists) {
                const surebox = document.createElement("div");
                surebox.classList.add("surebox");
                surebox.style.position = "absolute";
                surebox.style.left = (parseInt(e.clientX) + parseInt(window.scrollX) - 80).toString() + "px";
                surebox.style.top = (parseInt(e.clientY) + parseInt(window.scrollY) + 50).toString() + "px";
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
                        window.location.href = "/events";
                    });
                });
            }
        });
    }
    closeSureOptions()
}

function deleteComment() {
    const deleteCommentButtons = document.querySelectorAll(".fake.button.delete-comment");
    deleteCommentButtons.forEach((deleteCommentButton) => {
        deleteCommentButton.addEventListener("click", (e) => {
            const commentId = deleteCommentButton.id;
            const sureboxExists = deleteCommentButton.parentElement.querySelector(".surebox");
            if (!sureboxExists) {
                const surebox = document.createElement("div");
                surebox.classList.add("surebox");
                surebox.style.position = "absolute";
                surebox.style.left = (parseInt(e.clientX) + parseInt(window.scrollX) + 100).toString() + "px";
                surebox.style.top = (parseInt(e.clientY) + parseInt(window.scrollY) - 20).toString() + "px";
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
                    sendAjaxRequest('DELETE', `/comment/${commentId}/delete`, null, function () {});
                    surebox.remove();
                    deleteCommentButton.parentElement.parentElement.parentElement.remove();
                });
            }
        });
    });
    closeSureOptions()
}

function editComment() {
    const editCommentButtons = document.querySelectorAll(".fake.button.edit-comment");
    editCommentButtons.forEach((editCommentButton) => {
        editCommentButton.addEventListener("click", (e) => {
            const commentId = editCommentButton.id;
            const eventId = window.location.href.split("/")[4].split("#")[0];
            console.log(eventId);
            console.log(commentId);
            editCommentButton.parentElement.style.display = "none";
            const mainCommentDiv = editCommentButton.parentElement.parentElement;
            const commentText = mainCommentDiv.querySelector("p");
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
            mainCommentDiv.insertBefore(editCommentDiv, mainCommentDiv.querySelector(".comment-actions"));

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
                    sendAjaxRequest('PUT', `/comment/${commentId}/update`, {comment: comment}, function () {});
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

function requestToJoin(){
    const requestsToJoin = document.querySelectorAll(".pending_request_to_join");
    requestsToJoin.forEach((requestToJoin) => {
        requestToJoin.addEventListener("click", () => {
            const requestToJoinId = requestToJoin.id;
            const decisionBox = requestToJoin.querySelector(".decision_box");
            if(!decisionBox){
                const decisionBox = document.createElement("div");
                decisionBox.classList.add("decision_box");
                decisionBox.innerHTML = `
                    <button type="button" class="accept_request_to_join">&check;</button>
                    <button type="button" class="decline_request_to_join">&#10060;</button>
                `;
                requestToJoin.appendChild(decisionBox);

                const acceptRequestToJoin = decisionBox.querySelector(".accept_request_to_join");
                acceptRequestToJoin.addEventListener("click", () => {
                    const requestsToJoinDiv = requestToJoin.parentElement;
                    requestToJoin.remove();
                    if(!requestsToJoinDiv.childElementCount){
                        const noRequestsToJoin = document.createElement("h4");
                        noRequestsToJoin.textContent = "No Requests To Join";
                        requestsToJoinDiv.appendChild(noRequestsToJoin);
                    }
                    sendAjaxRequest('POST', `/api/accept-request-to-join`, {id_requestToJoin: requestToJoinId}, function () {});
                });

                const declineRequestToJoin = decisionBox.querySelector(".decline_request_to_join");
                declineRequestToJoin.addEventListener("click", () => {
                    const requestsToJoinDiv = requestToJoin.parentElement;
                    requestToJoin.remove();
                    if(!requestsToJoinDiv.childElementCount){
                        const noRequestsToJoin = document.createElement("h4");
                        noRequestsToJoin.textContent = "No Requests To Join";
                        requestsToJoinDiv.appendChild(noRequestsToJoin);
                    }
                    sendAjaxRequest('POST', `/api/deny-request-to-join`, {id_requestToJoin: requestToJoinId}, function () {});
                });
            }

        });
    });
    closeDecisionBox();
}

function likeComment(){
    const likes = document.querySelectorAll(".comment-like");
    likes.forEach((like) => {
        like.addEventListener("click", () => {
            const commentId = like.parentElement.parentElement.parentElement.parentElement.id;
            const userId = like.id;
            const dislike = like.parentElement.querySelector(".comment-dislike");
            const likesNumber = like.parentElement.querySelector(".comment-like-number");
            const dislikesNumber = dislike.parentElement.querySelector(".comment-dislike-number");
            if(like.classList.contains("comment-like-active")){
                like.classList.remove("comment-like-active");
                dislike.classList.remove("comment-dislike-active");
                likesNumber ? likesNumber.textContent = parseInt(likesNumber.textContent) - 1 : null;
                like.src = "/icons/like.png";
                dislike.src = "/icons/like.png";
                //sendAjaxRequest('POST', `/api/comment/${commentId}/like`, {action: 'remove', id_comment: commentId, id_user: userId}, function () {});
            }
            else {
                like.classList.add("comment-like-active");
                if(dislike.classList.contains("comment-dislike-active")){
                    dislikesNumber ? dislikesNumber.textContent = parseInt(dislikesNumber.textContent) - 1 : null;
                }
                dislike.classList.remove("comment-dislike-active");
                likesNumber ? likesNumber.textContent = parseInt(likesNumber.textContent) + 1 : null;
                like.src = "/icons/blue_like.png";
                dislike.src = "/icons/like.png";
                //sendAjaxRequest('POST', `/api/comment/${commentId}/like`, {action: 'add', id_comment: commentId, id_user: userId}, function () {});
            }
        });
    });
}

function dislikeComment(){
    const dislikes = document.querySelectorAll(".comment-dislike");
    dislikes.forEach((dislike) => {
        dislike.addEventListener("click", () => {
            const commentId = dislike.parentElement.parentElement.parentElement.parentElement.id;
            const userId = dislike.id;
            const like = dislike.parentElement.querySelector(".comment-like");
            const dislikesNumber = dislike.parentElement.querySelector(".comment-dislike-number");
            const likesNumber = like.parentElement.querySelector(".comment-like-number");
            if(dislike.classList.contains("comment-dislike-active")){
                dislike.classList.remove("comment-dislike-active");
                like.classList.remove("comment-like-active");
                dislikesNumber ? dislikesNumber.textContent = parseInt(dislikesNumber.textContent) - 1 : null;
                dislike.src = "/icons/like.png";
                like.src = "/icons/like.png";
                //sendAjaxRequest('POST', `/api/comment/${commentId}/dislike`, {action: 'remove', id_comment: commentId, id_user: userId}, function () {});
            }
            else {
                dislike.classList.add("comment-dislike-active");
                if(like.classList.contains("comment-like-active")){
                    likesNumber ? likesNumber.textContent = parseInt(likesNumber.textContent) - 1 : null;
                }
                like.classList.remove("comment-like-active");
                dislikesNumber ? dislikesNumber.textContent = parseInt(dislikesNumber.textContent) + 1 : null;
                dislike.src = "/icons/blue_like.png";
                like.src = "/icons/like.png";
                //sendAjaxRequest('POST', `/api/comment/${commentId}/dislike`, {action: 'add', id_comment: commentId, id_user: userId}, function () {});
            }
        });
    });
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
requestToJoin();
likeComment();
dislikeComment();
