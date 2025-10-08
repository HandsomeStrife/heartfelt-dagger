/**
 * MEDIUM FIX: CSRF Token Utility
 * 
 * Provides robust CSRF token retrieval with error handling.
 * Throws error if token is missing instead of silently returning empty string.
 * 
 * TYPESCRIPT MIGRATION: Fully typed with strict null safety
 */

/**
 * Gets the CSRF token from the meta tag
 * @throws {Error} If CSRF token is not found
 * @returns The CSRF token
 */
export function getCSRFToken(): string {
  const tokenElement = document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]');
  const token = tokenElement?.content;

  if (!token || token.trim() === '') {
    throw new Error(
      'CSRF token not found or empty. Ensure <meta name="csrf-token" content="..."> exists in the document head.'
    );
  }

  return token;
}

/**
 * Gets the CSRF token safely, returning null if not found
 * @returns The CSRF token or null
 */
export function getCSRFTokenSafe(): string | null {
  const tokenElement = document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]');
  return tokenElement?.content || null;
}

