/**
 * MEDIUM FIX: Debounce utility for rate-limiting rapid function calls
 * 
 * Prevents excessive processing of rapid message updates by ensuring
 * the function is only called once after a specified delay.
 * 
 * TYPESCRIPT MIGRATION: Fully typed with generics for function signatures
 */

type DebouncedFunction<T extends (...args: any[]) => any> = (
  ...args: Parameters<T>
) => void;

/**
 * Debounces a function to prevent rapid repeated calls
 */
export function debounce<T extends (...args: any[]) => any>(
  func: T,
  wait: number
): DebouncedFunction<T> {
  let timeout: number | null = null;

  return function executedFunction(...args: Parameters<T>): void {
    const later = (): void => {
      if (timeout) {
        clearTimeout(timeout);
      }
      func(...args);
    };

    if (timeout) {
      clearTimeout(timeout);
    }
    timeout = setTimeout(later, wait);
  };
}

/**
 * MEDIUM FIX: Throttle utility for rate-limiting function calls
 * 
 * Ensures the function is called at most once per specified time period,
 * useful for expensive operations that need to run on rapid events.
 */
export function throttle<T extends (...args: any[]) => any>(
  func: T,
  limit: number
): DebouncedFunction<T> {
  let inThrottle = false;

  return function executedFunction(...args: Parameters<T>): void {
    if (!inThrottle) {
      func(...args);
      inThrottle = true;
      setTimeout(() => (inThrottle = false), limit);
    }
  };
}

