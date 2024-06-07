package main

import (
	"context"
	"errors"
	"fmt"
	"math/rand"
	"time"

	"github.com/segmentio/ksuid"
)

// Приложение эмулирует получение и обработку неких тасков. Пытается и получать, и обрабатывать в многопоточном режиме.
// Приложение должно генерировать таски 10 сек. Каждые 3 секунды должно выводить в консоль результат всех обработанных к этому моменту тасков (отдельно успешные и отдельно с ошибками).

// ЗАДАНИЕ: сделать из плохого кода хороший и рабочий - as best as you can.
// Важно сохранить логику появления ошибочных тасков.
// Важно оставить асинхронные генерацию и обработку тасков.
// Сделать правильную мультипоточность обработки заданий.
// Обновленный код отправить через pull-request в github
// Как видите, никаких привязок к внешним сервисам нет - полный карт-бланш на модификацию кода.

// A Task represents a meaninglessness of our life
type Task struct {
	id           string
	createdTime  time.Time
	finishedTime time.Time
	creationErr  error
}

type Result struct {
	task Task
	err  error
}

func GenerateKSUID() string {
	return ksuid.New().String()
}

func generateTasks(ctx context.Context) <-chan Task {
	tasks := make(chan Task, 10)
	go func() {
		defer close(tasks)

		timeToGo := false
		for {
			if timeToGo {
				break
			}
			task := Task{
				id:           GenerateKSUID(),
				createdTime:  time.Now(),
				finishedTime: time.Time{},
				creationErr:  nil,
			}
			if rand.Intn(2) > 0 { // вот такое условие появления ошибочных тасков
				task.creationErr = errors.New("some error occurred")
			}

			select {
			case <-ctx.Done():
				timeToGo = true
			case tasks <- task:
			}
		}
	}()
	return tasks
}

func doTask(task Task) Result {
	var err error
	if task.creationErr == nil {
		err = nil
	} else {
		err = errors.New("something went wrong")
	}
	task.finishedTime = time.Now()

	time.Sleep(time.Millisecond * 150)
	return Result{
		task: task,
		err:  err,
	}
}

func main() {
	ctx, cancel := context.WithTimeout(context.Background(), 500*time.Millisecond)
	defer cancel()

	tasksChan := generateTasks(ctx)

	done := make(chan Result)
	var errs []error
	completedTasks := make(map[string]Task)
	timeToGo := false
	for {
		if timeToGo {
			break
		}
		select {
		case task := <-tasksChan:
			go func(ctx context.Context, task2 Task) {
				result := doTask(task2)
				select {
				case <-ctx.Done():
				case done <- result:
				}
			}(ctx, task)
		case result := <-done:
			if result.err != nil {
				err := fmt.Errorf("task id %s time %s, error %w",
					result.task.id, result.task.createdTime.Format(time.RFC3339), result.err)
				errs = append(errs, err)
			} else {
				completedTasks[result.task.id] = result.task
			}
		case <-ctx.Done():
			done = nil
			timeToGo = true
		}
	}

	println("Errors:")
	for _, err := range errs {
		fmt.Println(err)
	}

	println("Done tasks:")
	for id := range completedTasks {
		fmt.Println(id)
	}
}
