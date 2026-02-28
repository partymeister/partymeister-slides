export function useTextResize() {
  // Creates a hidden measurement div to calculate text overflow
  function resizeText(
    content: string,
    maxWidth: number,
    maxHeight: number,
    startFontSize: number,
    fontFamily: string,
    fontWeight: string,
    lineHeight: string,
  ): number {
    const MIN_FONT = 5

    // Create off-screen measurement container
    const measurer = document.createElement('div')
    measurer.style.position = 'absolute'
    measurer.style.left = '-9999px'
    measurer.style.top = '-9999px'
    measurer.style.width = maxWidth + 'px'
    measurer.style.fontFamily = fontFamily
    measurer.style.fontWeight = fontWeight
    measurer.style.lineHeight = lineHeight
    measurer.style.wordWrap = 'break-word'
    measurer.style.overflow = 'hidden'
    measurer.style.visibility = 'hidden'
    measurer.innerHTML = content
    document.body.appendChild(measurer)

    let fontSize = startFontSize

    // Shrink until it fits
    measurer.style.fontSize = fontSize + 'px'
    while (fontSize > MIN_FONT && measurer.scrollHeight > maxHeight) {
      fontSize--
      measurer.style.fontSize = fontSize + 'px'
    }

    document.body.removeChild(measurer)
    return fontSize
  }

  return { resizeText }
}
