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

        // Append the new events to the container
        const eventsContainer = document.getElementById('eventsContainer');
        if(eventsContainer){
          events.forEach(event => {
            const eventCard = document.createElement('div');
            eventCard.classList.add('event-card');
            
            eventCard.innerHTML = `
                          <a href="/events/${event.id}">
                            <h3>${event.name}</h3>
                            <p>${event.description}</p>
                          </a>
                      `;
            eventsContainer.appendChild(eventCard);
          });

          // Update the page number for the next request
          page++;

          // If there are more pages, continue to listen for scroll events
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
            sendAjaxRequest('POST', `/api/user/manage-event/${id_event}`, {actionName: 'pin', pinAction: pinAction}, function(){window.location.reload();});
          });

          const hideButton = document.createElement("button");
          hideButton.type = "button";
          hideButton.textContent = hideButtonText;
          hideButton.addEventListener("click", () => {
            sendAjaxRequest('POST', `/api/user/manage-event/${id_event}`, {actionName: 'hide', hideAction: hideAction}, function(){ window.location.reload();});
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

addEventListeners();
openOptions();
closeOptions();