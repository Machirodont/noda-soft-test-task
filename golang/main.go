package main

import (
	"context"
	"fmt"
	"sync"
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
	cT         string // время создания
	fT         string // время выполнения
	taskRESULT []byte
}

func (t Ttype) String() string {
	return fmt.Sprintf(
		"ID: %d, start: %s, finish: %s, message: %s",
		t.id, t.cT, t.fT, string(t.taskRESULT),
	)
}

func isOccurredTask(task Ttype) bool {
	_, err := time.Parse(time.RFC3339, task.cT)

	return err != nil
}

func isWrongTask(task Ttype) bool {
	return string(task.taskRESULT[14:]) != "successed"
}

func main() {
	producer := NewProducer()
	ch := producer.Run()

	aggregated := NewTaskAggregated()
	worker := NewTaskWorker(2, aggregated)

	go func() {
		time.Sleep(3 * time.Second)
		producer.Stop()
	}()

	worker.Run(ch)

	for _, task := range aggregated.Success() {
		fmt.Println(task)
	}

	for _, err := range aggregated.Errors() {
		fmt.Println(err)
	}
}

type Producer struct {
	ctx        context.Context
	cancelFunc context.CancelFunc
}

func NewProducer() *Producer {
	return &Producer{}
}

func (p *Producer) Run() <-chan Ttype {
	p.ctx, p.cancelFunc = context.WithCancel(context.Background())
	ch := make(chan Ttype, 10)

	go func() {
		i := 0
		for {
			select {
			case <-p.ctx.Done():
				close(ch)

				return
			default:
				i++
				ft := time.Now().Format(time.RFC3339)
				if time.Now().Nanosecond()%2 > 0 { // вот такое условие появления ошибочных тасков
					ft = "Some error occurred"
				}

				ch <- Ttype{cT: ft, id: i} // передаем таск на выполнение
			}
		}
	}()

	return ch
}

func (p *Producer) Stop() {
	p.cancelFunc()
}

type TaskWorker struct {
	countThreads   int
	taskAggregated *TaskAggregated
	wg             sync.WaitGroup
}

func NewTaskWorker(countThreads int, taskAggregated *TaskAggregated) *TaskWorker {
	return &TaskWorker{
		countThreads:   countThreads,
		taskAggregated: taskAggregated,
		wg:             sync.WaitGroup{},
	}
}

func (t *TaskWorker) Run(ch <-chan Ttype) {
	t.wg.Add(t.countThreads)

	for i := 0; i < t.countThreads; i++ {
		go t.runThread(ch)
	}

	t.wg.Wait()
}

func (t *TaskWorker) runThread(ch <-chan Ttype) {
	for task := range ch {
		if isOccurredTask(task) {
			task.taskRESULT = []byte("something went wrong")
		} else {
			task.taskRESULT = []byte("task has been successed")
		}

		task.fT = time.Now().Format(time.RFC3339Nano)

		time.Sleep(time.Millisecond * 150)

		t.taskAggregated.AddTask(task)
	}

	t.wg.Done()
}

type TaskAggregated struct {
	successTask []Ttype
	errors      []error
	mutex       sync.Mutex
}

func NewTaskAggregated() *TaskAggregated {
	return &TaskAggregated{
		successTask: make([]Ttype, 0),
		errors:      make([]error, 0),
	}
}

func (t *TaskAggregated) AddTask(task Ttype) {
	t.mutex.Lock()
	defer t.mutex.Unlock()

	if isWrongTask(task) {
		t.errors = append(
			t.errors,
			fmt.Errorf("task id: %d time: %s, error: %s", task.id, task.cT, task.taskRESULT),
		)

		return
	}

	t.successTask = append(t.successTask, task)
}

func (t *TaskAggregated) Success() []Ttype {
	return t.successTask
}

func (t *TaskAggregated) Errors() []error {
	return t.errors
}

