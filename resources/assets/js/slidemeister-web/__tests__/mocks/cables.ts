export function createMockCables() {
  return {
    patch: {
      setVariable: vi.fn(),
    },
  }
}

export function getCablesSetVariableCalls(): Array<{ name: string; value: any }> {
  const mock = (globalThis as any).CABLES.patch.setVariable as ReturnType<typeof vi.fn>
  return mock.mock.calls.map(([name, value]: [string, any]) => ({ name, value }))
}

export function resetCablesMock(): void {
  ;(globalThis as any).CABLES.patch.setVariable.mockClear()
}
