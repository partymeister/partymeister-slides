import { openDB, type IDBPDatabase } from 'idb'
import { toRaw, isProxy } from 'vue'

const DB_NAME = 'slidemeister-db'
const STORE_NAME = 'storage'
const DB_VERSION = 1

let dbPromise: Promise<IDBPDatabase> | null = null

function getDb(): Promise<IDBPDatabase> {
  if (!dbPromise) {
    dbPromise = openDB(DB_NAME, DB_VERSION, {
      upgrade(db) {
        if (!db.objectStoreNames.contains(STORE_NAME)) {
          db.createObjectStore(STORE_NAME)
        }
      },
    })
  }
  return dbPromise
}

export function useStorage() {
  async function save<T>(key: string, value: T): Promise<void> {
    const db = await getDb()
    await db.put(STORE_NAME, JSON.parse(JSON.stringify(value)), key)
  }

  async function load<T>(key: string): Promise<T | null> {
    const db = await getDb()
    const result = await db.get(STORE_NAME, key)
    return (result as T) ?? null
  }

  async function remove(key: string): Promise<void> {
    const db = await getDb()
    await db.delete(STORE_NAME, key)
  }

  async function clear(): Promise<void> {
    const db = await getDb()
    await db.clear(STORE_NAME)
  }

  return { save, load, remove, clear }
}
