/*
 Copyright 2016 Google Inc. All Rights Reserved.
 Licensed under the Apache License, Version 2.0 (the "License");
 you may not use this file except in compliance with the License.
 You may obtain a copy of the License at
 http://www.apache.org/licenses/LICENSE-2.0
 Unless required by applicable law or agreed to in writing, software
 distributed under the License is distributed on an "AS IS" BASIS,
 WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 See the License for the specific language governing permissions and
 limitations under the License.
 */

// Names of the two caches used in this version of the service worker.
// Change to v2, etc. when you update any of the local resources, which will
// in turn trigger the install event again.
const PRECACHE = 'precache-v1';
const RUNTIME = 'runtime';

// A list of local resources we always want to be cached.
const PRECACHE_URLS = [
  'theme/vora/vendor/jqvmap/css/jqvmap.min.css',
  'theme/vora/vendor/chartist/css/chartist.min.css',
  'theme/vora/vendor/bootstrap-select/dist/css/bootstrap-select.min.css',
  'theme/vora/vendor/owl-carousel/owl.carousel.css',
  'theme/vora/css/style.css',
  'theme/vora/vendor/select2/css/select2.min.css',
  'theme/vora/vendor/fullcalendar-5.11.0/lib/main.css',
  'theme/vora/vendor/datatables/css/jquery.dataTables.min.css',
  'theme/vora/vendor/select2/css/select2.min.css',
  'theme/vora/css/bottom-navigation.css',
  'theme/vora/vendor/global/global.min.js',
  'theme/vora/vendor/bootstrap-select/dist/js/bootstrap-select.min.js',
  'https://kit.fontawesome.com/2ba10e709c.js',
  'theme/vora/vendor/chart.js/Chart.bundle.min.js',
  'theme/vora/vendor/owl-carousel/owl.carousel.js',
  'theme/vora/vendor/peity/jquery.peity.min.js',
  'theme/vora/vendor/jquery-nice-select/js/jquery.nice-select.min.js',
  'theme/vora/vendor/apexchart/apexchart.js',
  'theme/vora/js/dashboard/dashboard-1.js',
  'theme/vora/vendor/select2/js/select2.full.min.js',
  'theme/vora/js/plugins-init/select2-init.js',
  'theme/vora/js/custom.min.js',
  'theme/vora/js/dlabnav-init.js',
  'theme/vora/vendor/jqueryui/js/jquery-ui.min.js',
  'theme/vora/vendor/moment/moment.min.js',
  'theme/vora/vendor/fullcalendar-5.11.0/lib/main.min.js',
  'theme/vora/js/plugins-init/fullcalendar-init.js',
  'modules/excel/excel.js'
];

// The install handler takes care of precaching the resources we always need.
self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(PRECACHE)
      .then(cache => cache.addAll(PRECACHE_URLS))
      .then(self.skipWaiting())
  );
});

// The activate handler takes care of cleaning up old caches.
self.addEventListener('activate', event => {
  const currentCaches = [PRECACHE, RUNTIME];
  event.waitUntil(
    caches.keys().then(cacheNames => {
      return cacheNames.filter(cacheName => !currentCaches.includes(cacheName));
    }).then(cachesToDelete => {
      return Promise.all(cachesToDelete.map(cacheToDelete => {
        return caches.delete(cacheToDelete);
      }));
    }).then(() => self.clients.claim())
  );
});

// The fetch handler serves responses for same-origin resources from a cache.
// If no response is found, it populates the runtime cache with the response
// from the network before returning it to the page.
self.addEventListener('fetch', event => {
  // Skip cross-origin requests, like those for Google Analytics.
  if (event.request.url.startsWith(self.location.origin)) {
    event.respondWith(
      caches.match(event.request).then(cachedResponse => {
        if (cachedResponse) {
          return cachedResponse;
        }

        return caches.open(RUNTIME).then(cache => {
          return fetch(event.request).then(response => {
            // Put a copy of the response in the runtime cache.
            return cache.put(event.request, response.clone()).then(() => {
              return response;
            });
          });
        });
      })
    );
  }
});