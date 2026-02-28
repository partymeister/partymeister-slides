import type { EchoChannel } from '@/types/echo'

export class MockEchoChannel implements EchoChannel {
  private listeners = new Map<string, Function[]>()

  listen(event: string, callback: Function): this {
    if (!this.listeners.has(event)) {
      this.listeners.set(event, [])
    }
    this.listeners.get(event)!.push(callback)
    return this
  }

  emit(event: string, payload: any): void {
    const callbacks = this.listeners.get(event) || []
    callbacks.forEach((cb) => cb(payload))
  }

  stopListening(event: string): this {
    this.listeners.delete(event)
    return this
  }
}

export class MockEcho {
  channels = new Map<string, MockEchoChannel>()
  connector = { pusher: { connection: { state: 'connected' } } }

  channel(name: string): MockEchoChannel {
    if (!this.channels.has(name)) {
      this.channels.set(name, new MockEchoChannel())
    }
    return this.channels.get(name)!
  }

  leave(name: string): void {
    this.channels.delete(name)
  }

  disconnect(): void {
    this.channels.clear()
  }
}
