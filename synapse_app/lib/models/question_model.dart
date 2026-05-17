class QuestionModel {
  final String id;
  final String questionText;
  final String questionType; // 'multiple_choice' | 'true_false' | 'multiple_answer'
  final String optionA;
  final String optionB;
  final String optionC;
  final String optionD;
  final String? imageUrl;
  final int points;
  final String difficulty; // 'mudah' | 'sedang' | 'sulit'

  QuestionModel({
    required this.id,
    required this.questionText,
    required this.questionType,
    required this.optionA,
    required this.optionB,
    required this.optionC,
    required this.optionD,
    this.imageUrl,
    required this.points,
    required this.difficulty,
  });

  factory QuestionModel.fromJson(Map<String, dynamic> json) {
    return QuestionModel(
      id: json['id']?.toString() ?? json['_id']?.toString() ?? '',
      questionText: json['question']?.toString() ??
          json['question_text']?.toString() ??
          'Teks soal tidak ditemukan',
      questionType: json['question_type']?.toString() ?? 'multiple_choice',
      optionA: json['option_a']?.toString() ?? '',
      optionB: json['option_b']?.toString() ?? '',
      optionC: json['option_c']?.toString() ?? '',
      optionD: json['option_d']?.toString() ?? '',
      imageUrl: json['image_url']?.toString(),
      points: json['points'] != null
          ? int.tryParse(json['points'].toString()) ?? 10
          : 10,
      difficulty: json['difficulty']?.toString() ?? 'sedang',
    );
  }

  /// Helper: label tipe soal untuk display
  String get typeLabel {
    switch (questionType) {
      case 'true_false':
        return '✓✗ True/False';
      case 'multiple_answer':
        return '☑ Multi Answer';
      default:
        return '📝 Pilihan Ganda';
    }
  }

  /// Helper: label kesulitan dengan emoji
  String get difficultyLabel {
    switch (difficulty) {
      case 'mudah':
        return '🟢 Mudah';
      case 'sulit':
        return '🔴 Sulit';
      default:
        return '🟡 Sedang';
    }
  }

  /// Helper: cek apakah ini soal multi-answer (jawaban bisa lebih dari 1)
  bool get isMultiAnswer => questionType == 'multiple_answer';

  /// Helper: cek apakah ini soal true/false
  bool get isTrueFalse => questionType == 'true_false';
}