import { openDB } from 'idb';

let dbPromise = null;

export function getDB() {
    if (!dbPromise) {
        // Create a new database or open an existing one
        console.log('[DB] Opening database');
        dbPromise = openDB('my-app-db', 1, {
            upgrade(db) {
                db.createObjectStore('storage');
            }
        });
    }
    return dbPromise;
}

export async function saveToStorage(key, value) {
    console.log('[DB] Saving to storage', key, value);
    const db = await getDB();
    return db.put('storage', value, key);
}

export async function getFromStorage(key) {
    console.log('[DB] Getting from storage', key);
    const db = await getDB();
    return db.get('storage', key);
}

export async function deleteFromStorage(key) {
    console.log('[DB] Deleting from storage', key);
    const db = await getDB();
    return db.delete('storage', key);
}

export async function clearStorage() {
    console.log('[DB] Clearing storage');
    const db = await getDB();
    return db.clear('storage');
}
