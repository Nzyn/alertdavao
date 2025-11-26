# FAQ Implementation Guide

## Overview
This document outlines the implementation of the FAQ feature in the AlertDavao app, including a pinned FAQ profile in the chat list and an interactive FAQ screen.

## Files Created/Modified

### 1. **faq.json** (New)
- **Location**: `UserSide/assets/faq.json`
- **Purpose**: Stores all FAQ questions and answers
- **Format**: JSON with 20 Q&A pairs
- **Usage**: Loaded dynamically in FAQScreen component

### 2. **FAQScreen.tsx** (New)
- **Location**: `UserSide/app/(tabs)/FAQScreen.tsx`
- **Features**:
  - Welcome message greeting
  - Horizontal scrollable question bubbles
  - Click to send question as a message in the chat
  - Answer displays with typing animation
  - Navigation buttons to scroll through questions (prev/next)
  - "Ask Another Question" button to return to question selection
  - Full message history display

### 3. **chatlist.tsx** (Modified)
- **Location**: `UserSide/app/(tabs)/chatlist.tsx`
- **Changes**:
  - Added `renderFAQProfile()` function
  - FAQ profile rendered at top of chat list using `ListHeaderComponent`
  - FAQ profile displays:
    - Red help-circle icon
    - "FAQ" name with "PINNED" badge
    - Subtitle: "Get instant answers to common questions"
    - Highlighted background to stand out

## Features

### FAQ Profile in Chat List
- **Pinned at top** of all conversations
- **Visual distinction** with red background and help icon
- **Always accessible** from chat list
- **"PINNED" badge** indicates it's always available

### FAQ Screen Features

#### Question Selection
- Horizontal scrollable bubbles displaying all 20 questions
- Touch-friendly bubble design
- Dark blue bubbles with white text
- Auto-load on screen entry

#### Answer Display
- Questions appear as user messages (blue, right-aligned)
- Answers appear as FAQ messages (gray, left-aligned)
- Typing animation for answers
- Each character appears with 60ms delay
- Word-by-word typing effect for natural reading experience

#### Navigation
- **Previous/Next buttons**: Navigate through questions without selecting again
- **Ask Another**: Return to question selection view
- Circular navigation (last question loops to first)

#### Message History
- Full conversation history displayed
- Messages timestamped
- Clean, chat-like interface

## Technical Details

### Component Structure

```typescript
// FAQScreen.tsx component uses:
- useState for state management
- useEffect for FAQ loading
- FlatList for message rendering
- ScrollView for horizontal bubble scrolling
- Custom typing animation logic
```

### Styling
- Uses existing `styles` from `styles.ts`
- Custom inline styles for question bubbles
- Navigation buttons with border styling
- Message styling consistent with ChatScreen

### FAQ Data Loading
```typescript
loadFAQ() {
  const faqData = require('../../assets/faq.json');
  setFaqList(faqData.faq);
}
```

## Navigation Flow

```
Chat List
    ↓
FAQ Profile (Pinned at Top)
    ↓
FAQ Screen
    ├─ Select Question → Shows Question Bubble
    ├─ Question appears in chat
    ├─ Answer types out with animation
    ├─ Navigate with prev/next buttons
    └─ Ask Another to return to bubbles
```

## User Experience

1. **Initial View**: Welcome message + scrollable question bubbles
2. **Select Question**: Question added to message history, user message style
3. **View Answer**: Answer appears with typing animation
4. **Continue**: Use prev/next to navigate or "Ask Another" to start over
5. **Go Back**: Press back button to return to chat list

## Styling Colors

- **FAQ Avatar**: Red (#FF6B6B)
- **PINNED Badge**: Red (#FF6B6B)
- **Question Bubbles**: Dark Blue (#1D3557)
- **User Messages**: Dark Blue (#1D3557)
- **FAQ Messages**: Light Gray (#f0f0f0)

## FAQ Questions Included

1. How do I report an incident?
2. What are the requirements?
3. Can I edit my report?
4. How long for response?
5. How to check report status?
6. Is my data safe?
7. Is identity visible?
8. Location storage?
9. Encounter errors?
10. Anonymous reports?
11. Password reset?
12. Types of incidents?
13. 24/7 monitoring?
14. Data access?
15. Browser/device support?
16. Accidentally submit wrong info?
17. Verify report received?
18. Data usage?
19. Delete/cancel reports?
20. Other concerns?

## Future Enhancements

- Search functionality for questions
- Categorized questions
- Rating system for answer usefulness
- Share answer functionality
- Offline FAQ loading
- Multiple language support

## Testing Checklist

- [ ] FAQ appears at top of chat list
- [ ] "PINNED" badge visible
- [ ] Clicking FAQ opens FAQScreen
- [ ] Questions display as scrollable bubbles
- [ ] Selected question appears in message history
- [ ] Answer types out with animation
- [ ] Previous/Next navigation works
- [ ] Ask Another returns to bubble view
- [ ] Back button returns to chat list
- [ ] All 20 questions are accessible
