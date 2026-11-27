// Offline IndexedDB queue for SPIN MEIS 2.0 field forms
const SPIN_DB_NAME = 'spin_meis_offline';
const SPIN_STORE = 'pending_entries';

function openSpinDb() {
  return new Promise((resolve, reject) => {
    const req = indexedDB.open(SPIN_DB_NAME, 1);
    req.onupgradeneeded = () => {
      const db = req.result;
      if (!db.objectStoreNames.contains(SPIN_STORE)) {
        db.createObjectStore(SPIN_STORE, { keyPath: 'client_generated_id' });
      }
    };
    req.onsuccess = () => resolve(req.result);
    req.onerror = () => reject(req.error);
  });
}

window.spinQueueOfflineEntry = async function (payload) {
  const db = await openSpinDb();
  const tx = db.transaction(SPIN_STORE, 'readwrite');
  tx.objectStore(SPIN_STORE).put(payload);
  return new Promise((resolve) => {
    tx.oncomplete = () => resolve(true);
  });
};

window.spinSyncOfflineEntries = async function () {
  if (!navigator.onLine) return;
  const db = await openSpinDb();
  const tx = db.transaction(SPIN_STORE, 'readonly');
  const req = tx.objectStore(SPIN_STORE).getAll();
  req.onsuccess = async () => {
    for (const item of req.result || []) {
      try {
        const res = await fetch('/api/v1/offline-sync', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
          body: JSON.stringify(item),
        });
        if (res.ok || res.status === 409) {
          const delTx = db.transaction(SPIN_STORE, 'readwrite');
          delTx.objectStore(SPIN_STORE).delete(item.client_generated_id);
        }
      } catch (_) {}
    }
  };
};

window.addEventListener('online', () => window.spinSyncOfflineEntries());
