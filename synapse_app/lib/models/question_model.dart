class QuestionModel {
  final String id;
  final String questionText;
  final String optionA;
  final String optionB;
  final String optionC;
  final String optionD;

  QuestionModel({
    required this.id,
    required this.questionText,
    required this.optionA,
    required this.optionB,
    required this.optionC,
    required this.optionD,
  });

  factory QuestionModel.fromJson(Map<String, dynamic> json) {

    print("DATA SOAL DARI LARAVEL: $json");
    return QuestionModel(
      id: json['id']?.toString() ?? json['_id']?.toString() ?? '',
      questionText: json['question']?.toString() ?? json['question_text']?.toString() ?? 'Teks soal tidak ditemukan',
      optionA: json['option_a']?.toString() ?? '',
      optionB: json['option_b']?.toString() ?? '',
      optionC: json['option_c']?.toString() ?? '',
      optionD: json['option_d']?.toString() ?? '',
    );
  }
}