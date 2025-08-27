# Public Room Access Examples

This document demonstrates the new public room access functionality.

## Non-Campaign Room Examples

### 1. Passwordless Non-Campaign Room
```
Room URL: /rooms/ABC12345
Direct Access: ✅ No authentication required
Session URL: /rooms/ABC12345/session
Session Access: ✅ Anyone can view (read-only for non-participants)
```

### 2. Password-Protected Non-Campaign Room
```
Room URL: /rooms/DEF67890
Direct Access: ❌ Redirects to join page
Room URL with Password: /rooms/DEF67890?password=mysecret
Direct Access: ✅ Bypasses password prompt

Session URL: /rooms/DEF67890/session
Session Access: ❌ Redirects to join page
Session URL with Password: /rooms/DEF67890/session?password=mysecret
Session Access: ✅ Direct access to session
```

## Campaign Room Examples

### 3. Campaign Room (Any Password State)
```
Room URL: /rooms/GHI34567
Unauthenticated Access: ❌ Redirects to login
Authenticated Member Access: ✅ Normal access
Authenticated Non-Member Access: ❌ 403 Forbidden
```

## Join Flow Examples

### 4. Join Page Access
```
Join URL: /rooms/join/ABC12345
Unauthenticated Access: ✅ Can view join form
Join with Password: /rooms/join/ABC12345?password=mysecret
Unauthenticated Access: ✅ Can view join form (password pre-validated)

Actual Joining: ❌ Requires authentication
Redirects to: /login with message "Please log in to join this room"
```

## URL Structure

### Standard Room URLs
- **Room Overview**: `/rooms/{invite_code}`
- **Room Session**: `/rooms/{invite_code}/session`
- **Room Join**: `/rooms/join/{invite_code}`

### With Password Parameter
- **Room Overview**: `/rooms/{invite_code}?password={password}`
- **Room Session**: `/rooms/{invite_code}/session?password={password}`
- **Room Join**: `/rooms/join/{invite_code}?password={password}`

## Security Features

1. **Campaign Protection**: Campaign rooms always require authentication
2. **Password Security**: Passwords are hashed, never stored in plain text
3. **URL Parameters**: Passwords in URLs are checked against hashed values
4. **Guest Limitations**: Unauthenticated users can view but not modify
5. **Join Protection**: Actual room joining always requires user accounts

## Use Cases

- **Public Game Streams**: Share room URLs for viewers to watch sessions
- **Friend Groups**: Share password-protected room URLs directly
- **Conventions**: Passwordless rooms for open participation
- **Private Campaigns**: Campaign rooms with member-only access
