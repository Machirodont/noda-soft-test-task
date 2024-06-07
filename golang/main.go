package main

import (
	"context"
	"fmt"
	"log"
	"time"
)

// Приложение эмулирует получение и обработку неких тасков. Пытается и получать, и обрабатывать в многопоточном режиме.
// Приложение должно генерировать таски 10 сек. Каждые 3 секунды должно выводить в консоль результат всех обработанных к этому моменту тасков (отдельно успешные и отдельно с ошибками).

// ЗАДАНИЕ: сделать из плохого кода хороший и рабочий - as best as you can.
// Важно сохранить логику появления ошибочных тасков.
// Важно оставить асинхронные генерацию и обработку тасков.
// Сделать правильную мультипоточность обработки заданий.
// Обновленный код отправить через pull-request в github
// Как видите, никаких привязок к внешним сервисам нет - полный карт-бланш на модификацию кода.

// A Ttype represents a meaninglessness of our life
type Ttype struct {
	id         int
	createTime string // время создания
	finishTime string // время выполнения
	taskResult []byte
}

func generateTasks(ctx context.Context) chan Ttype {
	superChan := make(chan Ttype, 10)

	go func() {
		for {
			select {
			case <-ctx.Done():
				close(superChan)
				return
			default:
			}

			createTime := time.Now().Format(time.RFC3339)
			if time.Now().Nanosecond()%2 > 0 { // вот такое условие появления ошибочных тасков
				createTime = "Some error occured"
			}
			superChan <- Ttype{
				createTime: createTime,
				id:         int(time.Now().Unix()),
			} // передаем таск на выполнение
		}
	}()

	return superChan
}

func taskDo(task *Ttype) error {
	defer func() {
		task.finishTime = time.Now().Format(time.RFC3339Nano)
	}()

	_, err := time.Parse(time.RFC3339, task.createTime)
	if err != nil {
		task.taskResult = []byte("something went wrong")
		return fmt.Errorf("task id %d time %s, error %s", task.id, task.createTime, task.taskResult)
	}

	task.taskResult = []byte(fmt.Sprintf("task %v has been successed", task.id))

	time.Sleep(time.Millisecond * 150)

	return nil
}

func main() {
	ctx, cancelFunc := context.WithTimeout(context.Background(), 1000*time.Millisecond)
	defer cancelFunc()

	tasksChan := generateTasks(ctx)

	doneTasks := make([]Ttype, 0)
	undoneTasks := make([]error, 0)

	for task := range tasksChan {
		err := taskDo(&task)
		if err != nil {
			undoneTasks = append(undoneTasks, err)
		}

		doneTasks = append(doneTasks, task)
	}

	log.Println("Errors:")
	for _, err := range undoneTasks {
		log.Println(err)
	}

	log.Println("Done tasks:")
	for _, task := range doneTasks {
		log.Println(string(task.taskResult))
	}
}
