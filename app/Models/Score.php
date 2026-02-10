<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Score extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'registration_id',
        'rapor_semester_1',
        'rapor_semester_2',
        'rapor_semester_3',
        'rapor_semester_4',
        'rapor_semester_5',
        'rapor_average',
        'exam_math',
        'exam_science',
        'exam_indonesian',
        'exam_english',
        'exam_religion',
        'exam_average',
        'achievement_score',
        'achievement_description',
        'total_score',
        'rank',
        'rank_in_major',
        'is_passed',
        'notes',
        'inputted_by',
        'inputted_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'rapor_semester_1' => 'decimal:2',
        'rapor_semester_2' => 'decimal:2',
        'rapor_semester_3' => 'decimal:2',
        'rapor_semester_4' => 'decimal:2',
        'rapor_semester_5' => 'decimal:2',
        'rapor_average' => 'decimal:2',
        'exam_math' => 'decimal:2',
        'exam_science' => 'decimal:2',
        'exam_indonesian' => 'decimal:2',
        'exam_english' => 'decimal:2',
        'exam_religion' => 'decimal:2',
        'exam_average' => 'decimal:2',
        'achievement_score' => 'decimal:2',
        'total_score' => 'decimal:2',
        'rank' => 'integer',
        'rank_in_major' => 'integer',
        'is_passed' => 'boolean',
        'inputted_at' => 'datetime',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // Auto-calculate averages before saving
        static::saving(function ($score) {
            $score->calculateAverages();
        });
    }

    /**
     * Get the registration that owns this score.
     */
    public function registration(): BelongsTo
    {
        return $this->belongsTo(Registration::class);
    }

    /**
     * Get the user who inputted this score.
     */
    public function inputter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'inputted_by');
    }

    /**
     * Calculate rapor average.
     */
    public function calculateRaporAverage(): float
    {
        $scores = array_filter([
            $this->rapor_semester_1,
            $this->rapor_semester_2,
            $this->rapor_semester_3,
            $this->rapor_semester_4,
            $this->rapor_semester_5,
        ]);

        if (empty($scores)) {
            return 0;
        }

        return round(array_sum($scores) / count($scores), 2);
    }

    /**
     * Calculate exam average.
     */
    public function calculateExamAverage(): float
    {
        $scores = array_filter([
            $this->exam_math,
            $this->exam_science,
            $this->exam_indonesian,
            $this->exam_english,
            $this->exam_religion,
        ]);

        if (empty($scores)) {
            return 0;
        }

        return round(array_sum($scores) / count($scores), 2);
    }

    /**
     * Calculate total score.
     */
    public function calculateTotalScore(): float
    {
        $raporWeight = 0.6; // 60%
        $examWeight = 0.3;  // 30%
        $achievementWeight = 0.1; // 10%

        $totalScore = 
            ($this->rapor_average * $raporWeight) +
            ($this->exam_average * $examWeight) +
            ($this->achievement_score * $achievementWeight);

        return round($totalScore, 2);
    }

    /**
     * Calculate all averages and total score.
     */
    public function calculateAverages(): void
    {
        $this->rapor_average = $this->calculateRaporAverage();
        $this->exam_average = $this->calculateExamAverage();
        $this->total_score = $this->calculateTotalScore();
    }

    /**
     * Update rankings for all scores.
     */
    public static function updateRankings(): void
    {
        // Global ranking
        $scores = static::orderBy('total_score', 'desc')->get();
        $rank = 1;
        foreach ($scores as $score) {
            $score->rank = $rank++;
            $score->saveQuietly(); // Save without triggering events
        }

        // Ranking per major
        $majors = \App\Models\Major::all();
        foreach ($majors as $major) {
            $scores = static::whereHas('registration', function ($query) use ($major) {
                $query->where('major_id', $major->id);
            })->orderBy('total_score', 'desc')->get();

            $rank = 1;
            foreach ($scores as $score) {
                $score->rank_in_major = $rank++;
                $score->saveQuietly();
            }
        }
    }

    /**
     * Get rank with suffix (1st, 2nd, 3rd, etc).
     */
    public function getRankWithSuffixAttribute(): string
    {
        if (!$this->rank) {
            return '-';
        }

        $suffix = match($this->rank % 10) {
            1 => $this->rank % 100 === 11 ? 'th' : 'st',
            2 => $this->rank % 100 === 12 ? 'th' : 'nd',
            3 => $this->rank % 100 === 13 ? 'th' : 'rd',
            default => 'th',
        };

        return $this->rank . $suffix;
    }

    /**
     * Scope to get top scorers.
     */
    public function scopeTopScorers($query, int $limit = 10)
    {
        return $query->orderBy('total_score', 'desc')->limit($limit);
    }

    /**
     * Scope to get passed students.
     */
    public function scopePassed($query)
    {
        return $query->where('is_passed', true);
    }

    /**
     * Check if score is complete (all required fields filled).
     */
    public function isComplete(): bool
    {
        return $this->rapor_average > 0 && $this->total_score > 0;
    }
}
