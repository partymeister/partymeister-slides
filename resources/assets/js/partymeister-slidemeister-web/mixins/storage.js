import { openDB } from 'idb';

let dbPromise = null;

export function getDB() {
    if (!dbPromise) {
        dbPromise = openDB('my-app-db', 1, {
            upgrade(db) {
                db.createObjectStore('storage');
            }
        });
    }
    return dbPromise;
}

export async function saveToStorage(key, value) {
    const db = await getDB();
    return db.put('storage', value, key);
}

export async function getFromStorage(key) {
    const db = await getDB();
    return db.get('storage', key);
}

export async function deleteFromStorage(key) {
    const db = await getDB();
    return db.delete('storage', key);
}

export async function clearStorage() {
    const db = await getDB();
    return db.clear('storage');
}
