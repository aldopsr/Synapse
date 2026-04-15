class QuizModel {
  final String id;
  final String title;
  final int durationMinutes;

  QuizModel({
    required this.id,
    required this.title,
    required this.durationMinutes,
  });

  factory QuizModel.fromJson(Map<String, dynamic> json) {
    return QuizModel(
      id: json['id']?.toString() ?? json['_id']?.toString() ?? '',
      title: json['title']?.toString() ?? 'Kuis Tanpa Judul',
      durationMinutes: json['duration_minutes'] != null 
          ? int.tryParse(json['duration_minutes'].toString()) ?? 0 
          : 0,
    );
  }
}