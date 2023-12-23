(function () {
  window.addEventListener('load', function (event) {
    let disconnectBtn = document.getElementById('spf-admin-deauthorize');
    if (disconnectBtn) {
      disconnectBtn.addEventListener('click', (e) => {
        e.preventDefault();
        let row = document.querySelector('.spf_profile_row');
        let loader = document.getElementById('spf-loader');

        row.classList.add('hidden');
        loader.classList.remove('hidden');

        let xhr = new XMLHttpRequest(),
          data = new FormData();
        data.append('action', 'spf_disconnect_user');
        xhr.open('POST', window.spf.ajax_url, true);
        xhr.onreadystatechange = function () {
          if (xhr.readyState === XMLHttpRequest.DONE) {
            var status = xhr.status;
            if (status === 0 || (status >= 200 && status < 400)) {
              const result = JSON.parse(xhr.responseText);
              const elem = document.createElement('h3');
              elem.innerText = result;
              loader.parentNode.insertBefore(elem, loader);
              loader.classList.add('hidden');
              setTimeout(() => {
                window.location.reload(true);
              }, '1000');
            } else {
              console.error('Sorry, Something went wrong');
            }
          }
        };
        xhr.send(data);
      });
    }
  });
})();

(function () {
  window.addEventListener('load', function (event) {
    let clearCacheBtn = document.getElementById('spf-admin-clear-cache');
    if (clearCacheBtn) {
      clearCacheBtn.addEventListener('click', (e) => {
        e.preventDefault();
        let loader = document.getElementById('spf-loader-small');

        clearCacheBtn.classList.add('hidden');
        loader.classList.remove('hidden');

        let xhr = new XMLHttpRequest(),
          data = new FormData();
        data.append('action', 'spf_clear_feed_cache');
        xhr.open('POST', window.spf.ajax_url, true);
        xhr.onreadystatechange = function () {
          if (xhr.readyState === XMLHttpRequest.DONE) {
            var status = xhr.status;
            if (status === 0 || (status >= 200 && status < 400)) {
              const result = JSON.parse(xhr.responseText);
              const elem = document.createElement('p');
              elem.innerHTML = '<strong>' + result + '</strong>';
              clearCacheBtn.parentNode.insertBefore(elem, loader);
              loader.classList.add('hidden');
            } else {
              console.error('Sorry, Something went wrong');
            }
          }
        };
        xhr.send(data);
      });
    }
  });
})();
