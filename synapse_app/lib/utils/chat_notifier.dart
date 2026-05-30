// lib/utils/chat_notifier.dart
// Global notifier untuk sync chat history antar screen
import 'package:flutter/foundation.dart';

class ChatNotifier {
  static final ValueNotifier<int> chatUpdated = ValueNotifier(0);

  static void notify() {
    chatUpdated.value++;
  }
}